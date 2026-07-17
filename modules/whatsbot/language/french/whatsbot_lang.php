<?php

defined('BASEPATH') || exit('No direct script access allowed');

// connect account
$lang['whatsbot'] = 'WhatsBot';
$lang['connect_account'] = 'Connecter le compte';
$lang['connect_whatsapp_business'] = 'Connecter Whatsapp Business';
$lang['campaigning'] = 'Campagne';
$lang['business_account_id_description'] = 'Votre ID de compte WhatsApp Business (WABA)';
$lang['access_token_description'] = 'Votre jeton d’accès utilisateur après vous être inscrit sur le portail des développeurs Facebook';
$lang['whatsapp_business_account_id'] = 'ID de compte Whatsapp Business';
$lang['whatsapp_access_token'] = 'Jeton d’accès Whatsapp';
$lang['webhook_callback_url'] = 'URL de rappel Webhook';
$lang['verify_token'] = 'Vérifier le jeton';
$lang['connect'] = 'Connecter';
$lang['whatsapp'] = 'Whatsapp';
$lang['one_click_account_connection'] = 'Connexion au compte en un clic';
$lang['connect_your_whatsapp_account'] = 'Connectez votre compte Whatsapp';
$lang['copy'] = 'Copier';
$lang['copied'] = 'Copié !!';
$lang['disconnect'] = 'Déconnecter';
$lang['number'] = 'Numéro';
$lang['number_id'] = 'ID du numéro';
$lang['quality'] = 'Qualité';
$lang['status'] = 'Statut';
$lang['business_account_id'] = 'ID de compte professionnel';
$lang['permissions'] = 'Permissions';
$lang['phone_number_id_description'] = 'ID du numéro de téléphone connecté à l’API WhatsApp Business. Si vous n’êtes pas sûr, vous pouvez utiliser une requête GET Phone Number ID pour le récupérer depuis l’API WhatsApp (https://developers.facebook.com/docs/whatsapp/business-management-api/manage-phone-numbers)';
$lang['phone_number_id'] = 'ID du numéro du téléphone enregistré sur WhatsApp';
$lang['update_details'] = 'Mettre à jour les détails';

$lang['bots'] = 'Bots';
$lang['bots_management'] = 'Gestion des bots';
$lang['create_template_base_bot'] = 'Créer un bot de modèle de base';
$lang['create_message_bot'] = 'Créer un bot de message';
$lang['type'] = 'Type';
$lang['message_bot'] = 'Bot de message';
$lang['new_template_bot'] = 'Nouveau bot de modèle';
$lang['new_message_bot'] = 'Nouveau bot de message';
$lang['bot_name'] = 'Nom du bot';
$lang['reply_text'] = 'Texte de réponse <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Texte qui sera envoyé au prospect ou au contact. Vous pouvez également utiliser {companyname}, {crm_url} ou tout autre champ de fusion personnalisé du prospect ou du contact, ou utiliser le signe \'@\' pour trouver les champs de fusion disponibles" data-placement="bottom"></i> <span class="text-muted">(Le nombre maximum de caractères autorisés est de 1024)</span>';
$lang['reply_type'] = 'Type de réponse';
$lang['trigger'] = 'Déclencheur';
$lang['header'] = 'En-tête';
$lang['footer_bot'] = 'Pied de page <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 60" data-placement="bottom"></i>';
$lang['bot_with_reply_buttons'] = 'Option 1 : Bot avec boutons de réponse';
$lang['bot_with_button_link'] = 'Option 2 : Bot avec lien de bouton - URL CTA';
$lang['button1'] = 'Bouton1 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 20" data-placement="bottom"></i>';
$lang['button1_id'] = 'ID du Bouton1 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 256" data-placement="bottom"></i>';
$lang['button2'] = 'Bouton2 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 20" data-placement="bottom"></i>';
$lang['button2_id'] = 'ID du Bouton2 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 256" data-placement="bottom"></i>';
$lang['button3'] = 'Bouton3 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 20" data-placement="bottom"></i>';
$lang['button3_id'] = 'ID du Bouton3 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 256" data-placement="bottom"></i>';
$lang['button_name'] = 'Nom du bouton <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Le nombre maximum de caractères autorisés est de 20" data-placement="bottom"></i>';
$lang['button_link'] = 'Lien du bouton';
$lang['enter_name'] = 'Entrer le nom';
$lang['select_reply_type'] = 'Sélectionner le type de réponse';
$lang['enter_bot_reply_trigger'] = 'Entrer le déclencheur de réponse du bot';
$lang['enter_header'] = 'Entrer l’en-tête';
$lang['enter_footer'] = 'Entrer le pied de page';
$lang['enter_button1'] = 'Entrer le bouton1';
$lang['enter_button1_id'] = 'Entrer l’ID du bouton1';
$lang['enter_button2'] = 'Entrer le bouton2';
$lang['enter_button2_id'] = 'Entrer l’ID du bouton2';
$lang['enter_button3'] = 'Entrer le bouton3';
$lang['enter_button3_id'] = 'Entrer l’ID du bouton3';
$lang['enter_button_name'] = 'Entrer le nom du bouton';
$lang['enter_button_url'] = 'Entrer l’URL du bouton';
$lang['on_exact_match'] = 'Bot de réponse : Sur correspondance exacte';
$lang['when_message_contains'] = 'Bot de réponse : Quand le message contient';
$lang['when_client_send_the_first_message'] = 'Réponse de bienvenue - quand le prospect ou le client envoie le premier message';
$lang['bot_create_successfully'] = 'Bot créé avec succès';
$lang['bot_update_successfully'] = 'Bot mis à jour avec succès';
$lang['bot_deleted_successfully'] = 'Bot supprimé avec succès';
$lang['templates'] = 'Modèles';
$lang['template_data_loaded'] = 'Modèles chargés avec succès';
$lang['load_templates'] = 'Charger les modèles';
$lang['template_management'] = 'Gestion des modèles';


// campaigns
$lang['campaign'] = 'Campagne';
$lang['campaigns'] = 'Campagnes';
$lang['send_new_campaign'] = 'Envoyer Nouvelle Campagne';
$lang['campaign_name'] = 'Nom de la Campagne';
$lang['template'] = 'Modèle';
$lang['scheduled_send_time'] = '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Par client, en fonction du fuseau horaire du contact" data-placement="left"></i>Heure d\'envoi planifiée';
$lang['scheduled_time_description'] = 'Par client, en fonction du fuseau horaire du contact';
$lang['ignore_scheduled_time_and_send_now'] = 'Ignorer l\'heure planifiée et envoyer maintenant';
$lang['template'] = 'Modèle';
$lang['leads'] = 'Leads';
$lang['delivered_to'] = 'Livré à';
$lang['read_by'] = 'Lu par';
$lang['variables'] = 'Variables';
$lang['body'] = 'Corps';
$lang['variable'] = 'Variable';
$lang['match_with_selected_field'] = 'Correspondre avec un champ sélectionné';
$lang['preview'] = 'Aperçu';
$lang['send_campaign'] = 'Envoyer la campagne';
$lang['send_to'] = 'Envoyer à';
$lang['send_campaign'] = 'Envoyer Campagne';
$lang['view_campaign'] = 'Voir Campagne';
$lang['campaign_daily_task'] = 'Tâche quotidienne de campagne';
$lang['back'] = 'Retour';
$lang['phone'] = 'Téléphone';
$lang['message'] = 'Message';
$lang['currently_type_not_supported'] = 'Actuellement, le type de modèle <strong> %s </strong> n\'est pas pris en charge !';
$lang['of_your'] = 'de vos ';
$lang['contacts'] = 'Contacts';
$lang['select_all_leads'] = 'Sélectionner tous les Leads';
$lang['select_all_note_leads'] = 'Si vous sélectionnez ceci, tous les futurs leads sont inclus dans cette campagne.';
$lang['select_all_note_contacts'] = 'Si vous sélectionnez ceci, tous les futurs contacts sont inclus dans cette campagne.';

$lang['verified_name'] = 'Nom Vérifié';
$lang['mark_as_default'] = 'Définir par défaut';
$lang['default_number_updated'] = 'ID du numéro de téléphone par défaut mis à jour avec succès';
$lang['currently_using_this_number'] = 'Utilisation actuellement de ce numéro';
$lang['leads'] = 'Leads';
$lang['pause_campaign'] = 'Mettre en Pause la Campagne';
$lang['resume_campaign'] = 'Reprendre la Campagne';
$lang['campaign_resumed'] = 'Campagne reprise';
$lang['campaign_paused'] = 'Campagne mise en pause';

//Template
$lang['body_data'] = 'Données du Corps';
$lang['category'] = 'Catégorie';

// Template bot
$lang['create_new_template_bot'] = 'Créer un nouveau bot modèle';
$lang['template_bot'] = 'Bot Modèle';
$lang['variables'] = 'Variables';
$lang['preview'] = 'Aperçu';
$lang['template'] = 'Modèle';
$lang['bot_content_1'] = 'Ce message sera envoyé au contact une fois que la règle de déclenchement est remplie dans le message envoyé par le contact.';
$lang['save_bot'] = 'Enregistrer le bot';
$lang['please_select_template'] = 'Veuillez sélectionner un modèle';
$lang['use_manually_define_value'] = 'Utiliser la valeur définie manuellement';
$lang['merge_fields'] = 'Champs de Fusion';
$lang['template_bot_create_successfully'] = 'Bot modèle créé avec succès';
$lang['template_bot_update_successfully'] = 'Bot modèle mis à jour avec succès';
$lang['text_bot'] = 'Bot Texte';
$lang['option_2_bot_with_link'] = 'Option 2 : Bot avec lien bouton - Appel à l\'Action (CTA) URL';
$lang['option_3_file'] = 'Option 3 : Bot avec fichier';
// Bot settings
$lang['bot'] = 'Bot';
$lang['bot_delay_response'] = 'Message envoyé lorsque le délai de réponse est attendu';
$lang['bot_delay_response_placeholder'] = 'Donnez-moi un instant, j\'aurai la réponse sous peu';

$lang['whatsbot'] = 'WhatsBot';

//campaigns
$lang['relation_type'] = 'Type de Relation';
$lang['select_all'] = 'Sélectionner tout';
$lang['total'] = 'Total';
$lang['merge_field_note'] = 'Utilisez le signe \'@\' pour ajouter des champs de fusion.';
$lang['send_to_all'] = 'Envoyer à Tous ';
$lang['or'] = 'OU';

$lang['convert_whatsapp_message_to_lead'] = 'Acquérir un Nouveau Lead Automatiquement (convertir les nouveaux messages whatsapp en lead)';
$lang['leads_status'] = 'Statut du Lead';
$lang['leads_assigned'] = 'Lead assigné';
$lang['whatsapp_auto_lead'] = 'Whatsapp Auto Lead';
$lang['webhooks_label'] = 'Les données whatsapp reçues seront renvoyées à';
$lang['webhooks'] = 'WebHooks';
$lang['enable_webhooks'] = 'Activer le Renvoi des WebHooks';
$lang['chat'] = 'Chat';
$lang['black_listed_phone_numbers'] = 'Numéros de téléphone blacklistés';
$lang['sent_status'] = 'Statut Envoyé';

$lang['active'] = 'Actif';
$lang['approved'] = 'Approuvé';
$lang['this_month'] = 'ce mois-ci';
$lang['open_chats'] = 'Chats Ouverts';
$lang['resolved_conversations'] = 'Conversations Résolues';
$lang['messages_sent'] = 'Messages envoyés';
$lang['account_connected'] = 'Compte connecté';
$lang['account_disconnected'] = 'Compte déconnecté';
$lang['webhook_verify_token'] = 'Jeton de vérification du Webhook';
// Chat integration
$lang['chat_message_note'] = 'Le message sera envoyé sous peu. Veuillez noter que si le contact est nouveau, il n\'apparaîtra pas dans cette liste tant que le contact ne commence pas à interagir avec vous !';

$lang['activity_log'] = 'Journal d\'Activité';
$lang['whatsapp_logs'] = 'Journaux Whatsapp';
$lang['response_code'] = 'Code de Réponse';
$lang['recorded_on'] = 'Enregistré le';
$lang['request_details'] = 'Détails de la Demande';
$lang['raw_content'] = 'Contenu Brut';
$lang['headers'] = 'En-têtes';
$lang['format_type'] = 'Type de Format';

// Permission section
$lang['show_campaign'] = 'Afficher la campagne';
$lang['clear_log'] = 'Effacer le Journal';
$lang['log_activity'] = 'Journaliser l\'Activité';
$lang['load_template'] = 'Charger le Modèle';

$lang['action'] = 'Action';
$lang['total_parameters'] = 'Total des Paramètres';
$lang['template_name'] = 'Nom du Modèle';
$lang['log_cleared_successfully'] = 'Journal effacé avec succès';
$lang['whatsbot_stats'] = 'Statistiques WhatsBot';

$lang['not_found_or_deleted'] = 'Non trouvé ou supprimé';
$lang['response'] = 'Réponse';

$lang['select_image'] = 'Sélectionner une image';
$lang['image'] = 'Image';
$lang['image_deleted_successfully'] = 'Image supprimée avec succès';
$lang['whatsbot_settings'] = 'Paramètres WhatsBot';
$lang['maximum_file_size_should_be'] = 'La taille de fichier maximale doit être ';
$lang['allowed_file_types'] = 'Types de fichiers autorisés : ';

$lang['send_image'] = 'Envoyer une Image';
$lang['send_video'] = 'Envoyer une Vidéo';
$lang['send_document'] = 'Envoyer un Document';
$lang['record_audio'] = 'Enregistrer un Audio';
$lang['chat_media_info'] = 'Plus d\'infos sur les Types de Contenu Supportés & Taille de Média Post-Processing';
$lang['help'] = 'Aide';

// v1.1.0
$lang['clone'] = 'Cloner';
$lang['bot_clone_successfully'] = 'Bot cloné avec succès';
$lang['all_chat'] = 'Tous les Chats';
$lang['from'] = 'De :';
$lang['phone_no'] = 'Numéro de Téléphone :';
$lang['supportagent'] = 'Agent de Support';
$lang['assign_chat_permission_to_support_agent'] = 'Attribuer l\'autorisation de chat uniquement à l\'agent de support';
$lang['enable_whatsapp_notification_sound'] = 'Activer le son de notification de chat WhatsApp';
$lang['notification_sound'] = 'Son de Notification';
$lang['trigger_keyword'] = 'Mot-Clé Déclencheur';
$lang['modal_title'] = 'Sélectionner l\'Agent de Support';
$lang['close_btn'] = 'Fermer';
$lang['save_btn'] = 'Enregistrer';
$lang['support_agent'] = 'Agent de Support';
$lang['change_support_agent'] = 'Changer l\'Agent de Support';
$lang['replay_message'] = 'Vous ne pouvez pas envoyer de message, 24 heures sont écoulées.';
$lang['support_agent_note'] = '<strong>Remarque : </strong>Lorsque vous activez la fonction d\'agent de support, l\'attributaire du lead sera automatiquement affecté au chat. Les administrateurs peuvent également attribuer un nouvel agent depuis la page de chat.';
$lang['permission_bot_clone'] = 'Cloner le Bot';
$lang['remove_chat'] = 'Supprimer le Chat';
$lang['default_message_on_no_match'] = 'Réponse par Défaut - si aucun mot-clé ne correspond';
$lang['default_message_note'] = '<strong>Remarque : </strong>Activer cette option augmentera votre charge de webhook. Pour plus d\'informations, visitez ce <a href="https://docs.corbitaltech.dev/products/whatsbot/index.html" target="_blank">lien</a>.';

$lang['whatsbot_connect_account'] = 'Compte WhatsBot Connecté';
$lang['whatsbot_message_bot'] = 'Bot de Message WhatsBot';
$lang['whatsbot_template_bot'] = 'Bot de Modèle WhatsBot';
$lang['whatsbot_template'] = 'Modèle WhatsBot';
$lang['whatsbot_campaigns'] = 'Campagnes WhatsBot';
$lang['whatsbot_chat'] = 'Chat WhatsBot';
$lang['whatsbot_log_activity'] = 'Journal d\'Activité WhatsBot';
$lang['message_templates_not_exists_note'] = 'Permission de modèle méta manquante. Veuillez l\'activer dans votre compte Meta.';

// v1.2.0
$lang['ai_prompt'] = 'Invites IA';
$lang['ai_prompt_note'] = 'Pour les invites IA, veuillez saisir un message pour activer la fonction, ou utilisez des invites IA si déjà activées';
$lang['emojis'] = 'Émojis';
$lang['translate'] = 'Traduire';
$lang['change_tone'] = 'Changer de Ton';
$lang['professional'] = 'Professionnel';
$lang['friendly'] = 'Amical';
$lang['empathetic'] = 'Empathique';
$lang['straightforward'] = 'Franc';
$lang['simplify_language'] = 'Simplifier le Langage';
$lang['fix_spelling_and_grammar'] = 'Corriger l\'Orthographe & la Grammaire';

$lang['ai_integration'] = 'Intégration IA';
$lang['open_ai_api'] = 'API OpenAI';
$lang['open_ai_secret_key'] = 'Clé Secrète OpenAI - <a href="https://platform.openai.com/account/api-keys" target="_blank">Où trouver la clé secrète ?</a>';
$lang['chat_text_limit'] = 'Limite de Texte de Chat';
$lang['chat_text_limit_note'] = 'Pour optimiser les coûts opérationnels, envisagez de limiter le nombre de mots des réponses de chat d\'OpenAI';
$lang['chat_model'] = 'Modèle de Chat';
$lang['openai_organizations'] = 'Organisations OpenAi';
$lang['template_type'] = 'Type de Modèle';
$lang['update'] = 'Mettre à Jour';
$lang['open_ai_key_verification_fail'] = 'La Vérification de la Clé OpenAi est en Attente dans les paramètres, veuillez connecter votre compte OpenAI';
$lang['enable_wb_openai'] = 'Activer OpenAI dans le chat';
$lang['webhook_resend_method'] = 'Méthode de Renvoi du Webhook';
$lang['search_language'] = 'Rechercher une langue...';
$lang['document'] = 'Document';
$lang['select_document'] = 'Sélectionner le Document';
$lang['attchment_deleted_successfully'] = 'Pièce Jointe Supprimée Avec Succès';
$lang['attach_image_video_docs'] = 'Joindre des Images, Vidéos, Documents';
$lang['choose_file_type'] = 'Choisir le Type de Fichier';
$lang['max_size'] = 'Taille Max : ';
// v1.3.0

// CSV import
$lang['bulk_campaigns'] = 'Campagnes en Vrac';
$lang['upload_csv'] = 'Télécharger CSV';
$lang['upload'] = 'Télécharger';
$lang['csv_uploaded_successfully'] = 'Fichier CSV téléchargé avec succès';
$lang['please_select_file'] = 'Veuillez sélectionner un fichier CSV';
$lang['phonenumber_field_is_required'] = 'Le champ numéro de téléphone est requis';
$lang['out_of_the'] = 'Sur les';
$lang['records_in_your_csv_file'] = 'enregistrements dans votre fichier CSV,';
$lang['valid_the_campaign_can_be_sent'] = 'les enregistrements sont valides.<br /> La campagne peut être envoyée avec succès à ces';
$lang['users'] = 'utilisateurs';
$lang['campaigns_from_csv_file'] = 'Campagnes à partir du fichier CSV';
$lang['download_sample'] = 'Télécharger un Échantillon';
$lang['csv_rule_1'] = '1. <b>Exigence de la colonne Numéro de Téléphone :</b> Votre fichier CSV doit inclure une colonne nommée "Phoneno." Chaque enregistrement dans cette colonne doit contenir un numéro de contact valide, correctement formaté avec l\'indicatif du pays, incluant le signe "+" . <br /><br />';
$lang['csv_rule_2'] = '2. <b>Format et Encodage CSV :</b> Vos données CSV doivent suivre le format spécifié. La première ligne de votre fichier CSV doit contenir les en-têtes de colonne, comme indiqué dans le tableau d\'exemple. Assurez-vous que votre fichier est encodé en UTF-8 pour éviter tout problème d\'encodage.';
$lang['please_upload_valid_csv_file'] = 'Veuillez télécharger un fichier CSV valide';
$lang['please_add_valid_number_in_csv_file'] = 'Veuillez ajouter un <b>Phoneno</b> valide dans le fichier CSV';
$lang['total_send_campaign_list'] = 'Total des campagnes envoyées : %s';
$lang['sample_data'] = 'Données d\'Exemple';
$lang['firstname'] = 'Prénom';
$lang['lastname'] = 'Nom';
$lang['phoneno'] = 'Numéro de Téléphone';
$lang['email'] = 'Email';
$lang['country'] = 'Pays';
$lang['download_sample_and_read_rules'] = 'Télécharger le Fichier Échantillon & Lire les Règles';
$lang['please_wait_your_request_in_process'] = 'Veuillez patienter, votre demande est en cours de traitement.';
$lang['whatsbot_bulk_campaign'] = 'Campagnes en Vrac WhatsBot';
$lang['csv_campaign'] = 'Campagne CSV';

// Canned reply
$lang['canned_reply'] = 'Réponse Préréglée';
$lang['canned_reply_menu'] = 'Réponse Préréglée';
$lang['create_canned_reply'] = 'Créer une Réponse Préréglée';
$lang['title'] = 'Titre';
$lang['desc'] = 'Description';
$lang['public'] = 'Public';
$lang['action'] = 'Action';
$lang['delete_successfully'] = 'Réponse supprimée.';
$lang['cannot_delete'] = 'La réponse ne peut pas être supprimée.';
$lang['whatsbot_canned_reply'] = 'Réponse Préréglée WhatsBot';
$lang['reply'] = 'Répondre';

//AI Prompts
$lang['ai_prompts'] = 'Invites IA';
$lang['create_ai_prompts'] = 'Créer des Invites IA';
$lang['name'] = 'Nom';
$lang['action'] = 'Action';
$lang['prompt_name'] = 'Nom de l\'Invite';
$lang['prompt_action'] = 'Action de l\'Invite';
$lang['whatsbot_ai_prompts'] = 'Invites IA WhatsBot';

// new chat
$lang['replying_to'] = 'Répondant à :';
$lang['download_document'] = 'Télécharger le Document';
$lang['custom_prompt'] = 'Invite Personnalisée';
$lang['canned_replies'] = 'Réponses Préréglées';
$lang['use_@_to_add_merge_fields'] = 'Utilisez \'@\' pour ajouter des champs de fusion';
$lang['type_your_message'] = 'Tapez votre message';
$lang['you_cannot_send_a_message_using_this_number'] = 'Vous ne pouvez pas envoyer de message en utilisant ce numéro.';

// bot flow
$lang['bot_flow'] = 'Flux du Bot';
$lang['create_new_flow'] = 'Créer un Nouveau Flux';
$lang['flow_name'] = 'Nom du Flux';
$lang['flow'] = 'Flux';
$lang['bot_flow_builder'] = 'Constructeur de Flux du Bot';
$lang['you_can_not_upload_file_type'] = 'Vous ne pouvez pas télécharger le type de fichier <b> %s </b>';
$lang['whatsbot_bot_flow'] = 'Flux du Bot WhatsBot';

// v1.3.2
$lang['auto_clear_chat_history'] = 'Effacement Automatique de l\'Historique des Chats';
$lang['enable_auto_clear_chat_history'] = 'Activer l\'Effacement Automatique de l\'Historique des Chats';
$lang['auto_clear_time'] = 'Temps d\'Effacement Automatique de l\'Historique';
$lang['clear_chat_history_note'] = '<strong>Remarque : </strong> Si vous activez la fonction d\'effacement automatique de l\'historique des chats, cela effacera automatiquement l\'historique des chats en fonction du nombre de jours que vous spécifiez, chaque fois que le travail cron s\'exécute.';
$lang['source'] = 'Source';
$lang['groups'] = 'Groupes';

// v1.3.3
$lang['click_user_to_chat'] = 'Cliquez sur l\'utilisateur pour discuter';
$lang['searching'] = 'Recherche...';
$lang['filters'] = 'Filtres';
$lang['relation_type'] = 'Type de relation';
$lang['groups'] = 'Groupes';
$lang['source'] = 'Source';
$lang['status'] = 'Statut';
$lang['select_type'] = 'Sélectionnez le type';
$lang['select_agents'] = 'Sélectionnez les agents';
$lang['select_group'] = 'Sélectionnez le groupe';
$lang['select_source'] = 'Sélectionnez la source';
$lang['select_status'] = 'Sélectionnez le statut';
$lang['agents'] = 'Agents';

// v1.4.2
$lang['read_only'] = 'Lecture seule';

// v2.0.0
$lang['personal_assistant'] = 'AI Personal Assistant';
$lang['create_personal_assistant'] = 'Create AI Personal Assistant';
$lang['assistant_name'] = 'Assistant name';
$lang['pa_files'] = 'Upload files for AI analysis';
$lang['new_personal_assistant'] = 'New Personal Assistant';
$lang['edit_personal_assistant'] = 'Edit Personal Assistant';

$lang['click_to_get_qr_code'] = 'Click to get QR Code';
$lang['phone_numbers'] = 'Phone Numbers';
$lang['display_phone_number'] = 'Display Phone Number';
$lang['update_business_profile'] = 'Update Business Profile';
$lang['resync_phone_numbers'] = 'Re-sync Phone Numbers';
$lang['manage_phone_numbers'] = 'Manage Phone Numbers';
$lang['scan_qr_code_to_start_chat'] = 'Scan QR Code to Start Chat';
$lang['use_qr_code_to_invite'] = 'You can use the following QR Codes to invite people on this platform.';
$lang['url_for_qr_image'] = 'URL for QR Image';
$lang['whatsapp_url'] = 'WhatsApp URL';
$lang['whatsapp_now'] = 'WhatsApp Now';

$lang['file_upload_guidelines'] = 'File upload guidelines for best results';
$lang['supported_file_formats'] = 'Supported file formats';
$lang['pdf'] = 'PDF';
$lang['pdf_text'] = ': Only text-based PDFs (not scanned images).';
$lang['word'] = 'Word (DOC/DOCX)';
$lang['word_text'] = ': Text-based documents only (avoid images or scanned content).';
$lang['text'] = 'Text (TXT)';
$lang['text_text'] = ': Simple, plain text files with UTF-8 encoding.';
$lang['what_to_avoid'] = 'What to avoid';
$lang['scanned_images'] = 'Scanned Images';
$lang['scanned_images_text'] = ': Ensure documents are not image-based. Use OCR software for scanned PDFs.';
$lang['junk_characters'] = 'Junk Characters';
$lang['junk_characters_text'] = ': Avoid non-standard or corrupted characters in the document.';
$lang['large_files'] = 'Large Files';
$lang['large_files_text'] = ': Keep the file size reasonable for optimal performance.';
$lang['file_naming'] = 'File naming';
$lang['avoid_special_characters'] = 'Avoid Special Characters';
$lang['avoid_special_characters_text'] = ': Use alphanumeric characters and underscores in filenames (e.g., document_name.pdf).';
$lang['best_practices'] = 'Best practices';
$lang['well_structured_text'] = 'Use well-structured text with clear headings and paragraphs.';
$lang['proper_encoding'] = 'Ensure proper encoding (UTF-8) for text files.';
$lang['modal_processing_note'] = 'We are processing your document...';
$lang['incorrect_api_key_provided'] = 'Incorrect API key provided';
$lang['phone_number'] = 'Phone Number';
$lang['overall_health'] = 'Overall Health';
$lang['whatsApp_business_id'] = 'WhatsApp Business ID';
$lang['status_as_at'] = 'Status as at';
$lang['fb_app_id'] = 'Facebook App ID <a href="https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers#step-2--create-a-meta-app" class="mleft10 text-danger" target="_blank">HELP</a>';
$lang['fb_app_secret'] = 'Facebook App Secret';
$lang['facebook_developer_account_facebook_app'] = 'Step - 1 : Facebook Developer Account & Facebook App';
$lang['access_token_information'] = 'Access Token Information';
$lang['debug_token'] = 'Debug token';
$lang['permission_scopes'] = 'Permission scopes';
$lang['expiry_at'] = 'Expiry at';
$lang['disconnect_webhook'] = 'Disconnect Webhook';
$lang['webhook_configured'] = 'Webhook Configured';
$lang['connect_webhook'] = 'Connect Webhook';
$lang['webhook_subscribed_successfully'] = 'Webhook subscribe successfully';
$lang['can_send_message'] = 'Can Send Messages';
$lang['overall_health_send_message'] = 'Overall Health of Send Messages';
$lang['refresh_status'] = 'Refresh status';
$lang['issued_at'] = 'Issued at';
$lang['connect_whatsapp_business_account'] = 'Connect Whatsapp Business Account';
$lang['whatsApp_integration_setup'] = 'Step - 2 : WhatsApp Integration Setup';
$lang['openai_key_not_verified_note'] = 'This feature requires OpenAI key verification, which is currently pending. Please <a href="' . admin_url("settings?group=whatsbot&tab=ai_integration") . '" class="alert-link">Click here</a> to verify your API key.';
$lang['cant_upload_file_verification_pending'] = 'You can\'t upload file OpenAI KEY verification pending';

$lang['ai_assistant'] = 'AI Assistant';
$lang['enable_ai_assistant'] = 'Enable AI Assistant';
$lang['stop_ai_assistant'] = 'Keyword to stop AI Assistant';
$lang['temperature'] = 'Temperature:';
$lang['max_token'] = 'Max Token:';
$lang['ai_model'] = 'AI Model';
$lang['temperature_note'] = 'Adjusts the randomness of the models responses. Lower values make outputs more focused and predictable while higher values make them more creative and diverse.';
$lang['max_tokens_note'] = 'Sets the maximum number of tokens the model can generate. This includes both input and output tokens. A higher value allows for longer responses but may use more processing time.';
$lang['bot_with_reply_buttons'] = 'Option 2: Bot with reply buttons';
$lang['option_2_bot_with_link'] = 'Option 3: Bot with button link - Call to Action (CTA) URL';
$lang['option_3_file'] = 'Option 4: Bot with file';
$lang['option_1_personal_assitant'] = 'Option 1: Personal AI assistant';
$lang['whatsbot_pa'] = 'Whatsbot Personal Assistant';


$lang['disconnect_acount'] = 'Disconnect Account';
$lang['whatsapp_business_account'] = 'WhatsApp business account ';
$lang['access_token_information'] = 'Access Token Information';
$lang['refresh_health_status'] = 'Refresh health status';
$lang['facebook_developer_account_info'] = 'Facebook developer account information';
$lang['fb_config_id'] = 'Facebook Config ID <a href="https://developers.facebook.com/docs/whatsapp/embedded-signup/implementation#step-2--create-facebook-login-for-business-configuration" class="mleft10 text-danger" target="_blank">HELP</a>';
$lang['connect_with_facebook'] = 'Connect with Facebook';
$lang['send_test_message'] = 'Send Test Message';
$lang['test_number_note'] = 'Add `+` and the country code before the number (e.g., `+15551234567`) to send a WhatsApp message.';
$lang['wb_number'] = 'WhatsApp number';
$lang['send_message'] = 'Send message';
$lang['message_sent_successfully'] = 'Message sent sucessfully';
$lang['configure'] = 'Configure';
$lang['enable_embaded_signin'] = 'Enable embedded SignIn';
$lang['save_details'] = 'Save details';
$lang['modal_processing_connect_account_note'] = 'We are procesing on your request';
$lang['user_cancle_note'] = 'User cancelled login or did not fully authorize.';
$lang['access_token'] = 'Access token';
$lang['webhook_url'] = 'Webhook URL';
$lang['please_enter_all_details'] = 'Please enter all details';
$lang['please_select_default_number_first'] = 'Please select a default WhatsApp number to send the test message';
$lang['please_add_number_for_send_message'] = 'Please add a WhatsApp number to send the test message';
$lang['webhook_connected'] = 'Webhook connected successfully';

// Update language line : Start
$lang['update_version'] = 'Update Version';
$lang['update_warning'] = 'Before performing an update, it is <b>strongly recommended to create a full backup</b> of your current installation <b>(files and database)</b> and review the changelog.';
$lang['upgrade_function'] = 'Upgrade Function';
$lang['download_files'] = 'Download files';
$lang['fix_errors'] = 'Please fix the errors listed below.';
$lang['module_update'] = 'Module Update';
$lang['username'] = 'Username';
$lang['changelog'] = 'Change Log';
$lang['check_update'] = 'Check Update';
$lang['database_upgrade_required'] = 'Database upgrade is required!';
$lang['update_content_1'] = 'You need to perform a database upgrade before proceeding. Your ';
$lang['update_content_2'] = '<strong>files version</strong> is ';
$lang['update_content_3'] = ' and <strong>database version</strong> is ';
$lang['update_content_4'] = 'Make sure that you have a backup of your database before performing an upgrade.';
$lang['update_content_5'] = 'This message may show if you uploaded files from a newer version downloaded from CodeCanyon to your existing installation or you used an auto-upgrade tool.';
$lang['upgrade_now'] = 'UPGRADE NOW';
$lang['module_updated_successfully'] = 'Module Updated Successfully';
$lang['create_support_ticket'] = 'Create Support Ticket';
$lang['support_ticket_content'] = 'Do you want custom services? Visit here and create your ticket';
// Update language line: Over

$lang['verify_webhook'] = 'Verify Webhook';
$lang['webhook_received_successfully'] = 'Webhook received successfully';
$lang['sending'] = 'Sending ...';
$lang['verify'] = 'Verify';

$lang['flows'] = "Flows";
$lang['flow_data_loaded'] = 'Flows loaded successfully';
$lang['load_flows'] = 'Load Flows';
$lang['flow_management'] = 'Flow Management';
$lang['flow_templates'] = 'Flow Templates';
$lang['flow_responses'] = 'Flow Responses';
$lang['receiver'] = 'Receiver';
$lang['submit_time'] = 'Submit time';
$lang['whatsapp_no'] = 'Whatsapp number';

$lang['marketing_automation'] = "Marketing Automation";
$lang['after_ticket_status_changed'] = 'Ticket Status Changed';
$lang['project_status_changed'] = 'Project Status Changed';
$lang['add'] = 'Add';
$lang['automation'] = 'Automation';

$lang['after_ticket_status_changed_subtext'] = 'Send the selected WhatsApp flow to the client when the ticket status changes.';
$lang['project_status_changed_subtext'] = 'Send the selected WhatsApp flow to the client when project status changes';

$lang['sender_phone'] = 'Phone number use for message sending';

$lang['section'] = 'Section';
$lang['text'] = 'Text';
$lang['sub_text'] = 'Sub Text';
$lang['submit_button_label'] = 'Submit Button Label';
$lang['option_5_bot_with_options'] = 'Option 5: Bot With Options';
$lang['choose_options'] = 'Choose Option';

$lang['flow_response'] = 'Flow Response';
$lang['not_allowed_to_view'] = 'Not allowed to view';

$lang['send_location'] = 'Send Location';
$lang['search_for_a_place'] = 'Search for a place';
$lang['address_optional'] = 'Address (optional)';
$lang['latitude'] = 'Latitude';
$lang['longitude'] = 'Longitude';
$lang['drag_the_maker_to_set_the_location'] = 'Drag the marker to set the location or search for a place.';
$lang['use_my_location'] = 'Use My Current Location';
$lang['send_location'] = 'Send Location';

$lang['initiate_chat'] = 'Initiate Chat';
$lang['please_select_at_least_one_lead'] = 'Please select at least one lead';
$lang['chat_initiated_successfully'] = 'Chat Initiated  successfuly';
$lang['something_went_wrong'] = "Something Went Wrong";

$lang['video'] = "Video";
$lang['select_video'] = "Select Video";

$lang['whatsbot_cron'] = 'Whatsbot Cron';

// Session Management
$lang['session_management']                 = 'WhatsApp Session Management';
$lang['enable_session_management']          = 'Enable session management';
$lang['session_management_help_text']       = 'WhatsApp Cloud API has a 24-hour session limit for business-initiated messages. Enable this to send a reminder before the session expires.';
$lang['session_expiry_message']             = 'Session expiry message';
$lang['session_expiry_message_help']        = 'This message will be sent to customers before their 24-hour session expires.';
$lang['session_expiry_hours']               = 'Hours before expiry to send reminder';
$lang['session_expiry_hours_help']          = 'Set how many hours after the last customer message to send the reminder (max 23 hours).';
$lang['include_session_reset_button']       = 'Include quick reply button';
$lang['include_session_reset_button_help']  = 'Add a quick reply button to make it easier for customers to respond.';
$lang['session_management_note']            = 'Note: This feature requires the cron job to be properly configured. The cron job should run at least once per hour.';
$lang['session_reset_button_text']          = 'Continue Conversation';
$lang['whatsapp_session_management']        = 'WhatsApp Session Management';
$lang['active_sessions']                    = 'Active Sessions';
$lang['expiring_sessions']                  = 'Expiring Sessions';
$lang['sessions_reset_today']               = 'Sessions Reset Today';
$lang['sessions_reset_week']                = 'Sessions Reset (7 Days)';
$lang['session_management_disabled']        = 'WhatsApp Session Management is currently disabled.';

// Dashboard and Reports
$lang['session_reset_count']                = 'Session Reset Count';
$lang['session_expired']                    = 'Session Expired';
$lang['session_active']                     = 'Session Active';
$lang['session_reset_success_rate']         = 'Reset Success Rate';
$lang['customer_responded']                 = 'Customer Responded';
$lang['customer_not_responded']             = 'Customer Did Not Respond';
$lang['enable_now']                         = 'Enable Now';
