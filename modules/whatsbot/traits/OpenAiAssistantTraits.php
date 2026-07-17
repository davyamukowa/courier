<?php

namespace modules\whatsbot\traits;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OpenAIConfig;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use OpenAI;
use Exception;

trait OpenAiAssistantTraits
{
    protected ?OpenAIConfig $openAIConfig = null;
    protected ?string $docPath = null;

    /**
     * Retrieves the OpenAI API key from the options.
     *
     * @return string|null The OpenAI API key.
     */
    public function getOpenAiKey()
    {
        return get_option('wb_open_ai_key');
    }

    /**
     * Get AI Answer from Vector Store and Question
     *
     * @param string $fileName
     * @param string $question
     * @return string|null
     */
    public function getAIAnswer($assistant_id, $question) {
        $this->load->model('personal_assistant_model');
        $assistant = $this->personal_assistant_model->get($assistant_id);
        if (!$assistant || empty($assistant['openai_assistant_id'])) {
            return ['success' => false, 'message' => 'Assistant not found or not synced'];
        }

        try {
            // Create thread
            $thread = $this->createThread();
            if (!$thread) {
                throw new Exception('Failed to create thread');
            }

            // Add message to thread
            $message = $this->addMessageToThread($thread['id'], $question);
            if (!$message) {
                throw new Exception('Failed to add message to thread');
            }

            // Run assistant
            $run = $this->runAssistant($thread['id'], $assistant['openai_assistant_id']);
            if (!$run) {
                throw new Exception('Failed to run assistant');
            }

            // Wait for completion and get response
            $response = $this->waitForRunCompletion($thread['id'], $run['id']);
            
            return $response;
            // return ['success' => true, 'response' => $response];
        } catch (Exception $e) {
            return "Unable to get response. Try Again!";
        }
    }


    /**
     * Wait for run completion and get response
     */
    private function waitForRunCompletion($thread_id, $run_id, $max_attempts = 30) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        for ($i = 0; $i < $max_attempts; $i++) {
            // Check run status
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/threads/{$thread_id}/runs/{$run_id}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'OpenAI-Beta: assistants=v2'
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_code === 200) {
                $run = json_decode($response, true);
                
                if ($run['status'] === 'completed') {
                    // Get messages from thread
                    return $this->getThreadMessages($thread_id);
                }elseif(isset($run['status']) && $run['status'] === 'failed' && isset($run['last_error']['code']) && $run['last_error']['code'] === 'rate_limit_exceeded') {
                    log_activity("Assistant failed: ".$run['last_error']['message']);
                    throw new Exception('Run failed with status: ' . $run['status']); 
                }elseif (in_array($run['status'], ['failed', 'cancelled', 'expired'])) {
                    throw new Exception('Run failed with status: ' . $run['status']);
                }
            }

            sleep(1); // Wait 1 second before checking again
        }

        throw new Exception('Run timed out');
    }


    /**
     * Get messages from thread
     */
    private function getThreadMessages($thread_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/threads/{$thread_id}/messages");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $messages = json_decode($response, true);
            
            // Get the latest assistant message
            foreach ($messages['data'] as $message) {
                if ($message['role'] === 'assistant') {
                    return $message['content'][0]['text']['value'];
                }
            }
        }

        return false;
    }


    /**
     * Run assistant
     */
    private function runAssistant($thread_id, $assistant_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $data = ['assistant_id' => $assistant_id];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/threads/{$thread_id}/runs");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        return false;
    }


    /**
     * Create OpenAI thread
     */
    private function createThread() {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/threads');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        return false;
    }

    /**
     * Add message to thread
     */
    private function addMessageToThread($thread_id, $content) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $data = [
            'role' => 'user',
            'content' => $content
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/threads/{$thread_id}/messages");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        return false;
    }

    public function listModel(): array
    {
        try {
            $openAiKey = $this->getOpenAiKey();
            $openAi = new OpenAI();
            $client = $openAi->client($openAiKey);
            $response = $client->models()->list();

            if ($response === null || !is_object($response)) {
                throw new \RuntimeException('Invalid response format from OpenAI API.');
            }

            // Check for errors in response
            if (property_exists($response, 'error')) {
                update_option('wb_open_ai_key_verify', 0, 0);
                update_option('wb_openai_model', '', 0);
                return [
                    'status' => false,
                    'message' => $response->error->message ?? 'Unknown error occurred.',
                ];
            }

            // Update successful key verification
            update_option('wb_open_ai_key_verify', 1, 0);
            return [
                'status' => true,
                'data' => 'Model list fetched successfully.',
            ];
        } catch (\Throwable $th) {
            log_message('error', 'Error in listModel: ' . $th->getMessage());
            return [
                'status' => false,
                'message' => _l('incorrect_api_key_provided'),
            ];
        }
    }

    /**
     * Sends a request to the OpenAI API to get a response based on provided data.
     *
     * @param array $data The data to be sent to the OpenAI API.
     *
     * @return array Contains status and message of the response.
     */
    public function aiResponse(array $data)
    {
        try {
            $config = new OpenAIConfig();
            $config->apiKey = $this->getOpenAiKey();
            $config->model = get_option('wb_openai_model');
            $chat = new OpenAIChat($config);
            $message = $data['input_msg'];
            $menuItem = $data['menu'];
            $submenuItem = $data['submenu'];
            $status = true;

            $prompt = match ($menuItem) {
                'Simplify Language' => 'You will be provided with statements, and your task is to convert them to Simplify Language. but don\'t change inputed language.',
                'Fix Spelling & Grammar' => 'You will be provided with statements, and your task is to convert them to standard Language. but don\'t change inputed language.',
                'Translate' => 'You will be provided with a sentence, and your task is to translate it into ' . $submenuItem . ', only give translated sentance',
                'Change Tone' => 'You will be provided with statements, and your task is to change tone into ' . $submenuItem . '. but don\'t chnage inputed language.',
                'Custom Prompt' => $submenuItem,
            };

            $messages = [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $message],
            ];

            // Send the structured messages to OpenAI's chat API
            $response = $chat->generateChat($messages);
        } catch (\Throwable $th) {
            $status = false;
            $message = _l('something_went_wrong');
        }

        return [
            'status' => $status,
            'message' => $status ? $response : $message,
        ];
    }

    /**
     * Update OpenAI Assistant
     */
    public function updateOpenAIAssistant($assistant_id, $assistant_data) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }
        
        $data = [
            'name' => $assistant_data['name'],
            'description'  => $assistant_data['pa_description'],
            'instructions' => $assistant_data['pa_instruction'],
            'model'        => $assistant_data['assistant_model'],
            'temperature'  => isset($assistant_data['pa_temperature']) ? (float)$assistant_data['pa_temperature'] : 0.9,
            'tools' => [['type' => 'file_search']]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/assistants/{$assistant_id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code === 200;
    }


    /**
     * Create OpenAI Assistant
     */
    public function createOpenAIAssistant($assistant_data) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $data = [
            'name' => $assistant_data['name'],
            'description'  => !empty($assistant_data['pa_description']) ? $assistant_data['pa_description'] : "",
            'instructions' => !empty($assistant_data['pa_instruction']) ? $assistant_data['pa_instruction'] : "You are a helpful assistant. Please answer questions based on the provided documents.",
            'model'        => !empty($assistant_data['assistant_model']) ? $assistant_data['assistant_model'] : "gpt-4o-mini",
            'temperature'  => isset($assistant_data['pa_temperature']) && !empty($assistant_data['pa_temperature']) ? (float)$assistant_data['pa_temperature'] : 0.9,
            'tools' => [['type' => 'file_search']]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/assistants');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        log_message('error', 'Failed to create OpenAI assistant: ' . $response);
        return false;
    }


    /**
     * Attach file to OpenAI assistant
     */
    public function attachFileToAssistant($assistant_id, $file_id, $vector_store_id = null) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        if(empty($vector_store_id)){
            // Create vector store
            $vector_store = $this->createVectorStore($assistant_id);
            if (!$vector_store) {
                return false;
            }
            $vector_store_id = $vector_store['id'];
        }

        // Add file to vector store
        $data = ['file_ids' => [$file_id]];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/vector_stores/{$vector_store_id}/file_batches");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code === 200;
    }


    /**
     * Create vector store for assistant
     */
    public function createVectorStore($assistant_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $data = ['name' => 'Assistant Documents'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/vector_stores');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $vector_store = json_decode($response, true);
            
            // Update assistant with vector store
            $this->updateAssistantWithVectorStore($assistant_id, $vector_store['id']);
            
            return $vector_store;
        }

        return false;
    }


    /**
     * Update assistant with vector store
     */
    public function updateAssistantWithVectorStore($assistant_id, $vector_store_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $data = [
            'tool_resources' => [
                'file_search' => [
                    'vector_store_ids' => [$vector_store_id]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/assistants/{$assistant_id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->db->update(db_prefix() . 'wtc_personal_assistants', 
            ['openai_vector_id' => $vector_store_id], 
            ['openai_assistant_id' => $assistant_id], 
        );

        return $http_code === 200;
    }


    /**
     * Delete OpenAI Assistant
     */
    public function deleteOpenAIAssistant($assistant_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }
        // Deleting openAI assistant Vector Store
        $this->deleteAllVectorStoresOfAssistant($assistant_id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/assistants/{$assistant_id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code === 200;
    }


    /**
     * Upload file to OpenAI
     */
    public function uploadFileToOpenAI($file_path, $original_name) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key) || !file_exists($file_path)) {
            return false;
        }

        $ch = curl_init();
        $post_data = [
            'file' => new \CURLFile($file_path, mime_content_type($file_path), $original_name),
            'purpose' => 'assistants'
        ];

        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/files');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        log_message('error', 'Failed to upload file to OpenAI: ' . $response);
        return false;
    }

    /**
     * Delete OpenAI file
     */
    public function deleteOpenAIFile($file_id, $assistant_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/files/{$file_id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->deleteOpenAIFileFromVector($file_id, $assistant_id);

        return $http_code === 200;
    }

    /**
     * Delete OpenAI file
     */
    public function deleteOpenAIFileFromVector($file_id, $assistant_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return false;
        }

        $this->load->model('personal_assistant_model');
        $assistant = $this->personal_assistant_model->get($assistant_id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/vector_stores/{$assistant['openai_vector_id']}/files/{$file_id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code === 200;
    }

    /**
     * Delete all vector stores linked to an OpenAI Assistant
     * @param string $assistant_id
     * @return int HTTP status code (200 = OK, 404 = Not Found, etc.)
     */
    public function deleteAllVectorStoresOfAssistant($assistant_id) {
        $api_key = get_option('wb_open_ai_key');
        if (empty($api_key)) {
            return 401; // Unauthorized – API key missing
        }

        // Step 1: Get assistant details
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/assistants/{$assistant_id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'OpenAI-Beta: assistants=v2'
        ]);
        $assistant_response = curl_exec($ch);
        $assistant_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($assistant_http_code !== 200) {
            return $assistant_http_code; // e.g., 404 if assistant not found
        }

        $assistant_data = json_decode($assistant_response, true);
        $vector_store_ids = $assistant_data['tool_resources']['file_search']['vector_store_ids'] ?? [];

        if (empty($vector_store_ids)) {
            return 204; // No Content – nothing to delete
        }

        // Step 2: Loop and delete each vector store
        $final_http_code = 200;
        foreach ($vector_store_ids as $vs_id) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/vector_stores/{$vs_id}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'OpenAI-Beta: assistants=v2'
            ]);
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Track the last HTTP code (can be used for logging or error handling)
            $final_http_code = $http_code;
        }

        return $final_http_code;
    }
}
