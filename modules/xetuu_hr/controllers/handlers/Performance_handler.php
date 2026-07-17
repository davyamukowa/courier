<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/Xr_handler.php';

/**
 * Performance_handler
 * Routes all /xetuu_hr/performance/* URLs.
 */
class Performance_handler extends Xr_handler
{
    /** @var Xr_performance_model */
    private $perf;

    private $current_sub = '';

    public function __construct($ci)
    {
        parent::__construct($ci);
        $ci->load->model('xetuu_hr/Xr_performance_model', 'perf_mdl');
        $this->perf = $ci->perf_mdl;
        $ci->load->model('xetuu_hr/Xetuu_hr_model', 'hr_mdl');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Dispatcher
    // ══════════════════════════════════════════════════════════════════════════

    public function handle($sub, $action, $id)
    {
        $this->current_sub = (string) $sub;

        switch ($sub) {
            case '':
            case null:
                $this->_dashboard();
                break;
            case 'goals':
                $this->_goals($action, $id);
                break;
            case 'appraisals':
                $this->_appraisals($action, $id);
                break;
            case 'feedback':
                $this->_feedback($action, $id);
                break;
            case 'promotions':
                $this->_promotions($action, $id);
                break;
            case 'config':
                $this->_config($action, $id);
                break;
            default:
                $this->redirect($this->base() . '/performance');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Base view data
    // ══════════════════════════════════════════════════════════════════════════

    private function _base_data()
    {
        return [
            'xhr_active'        => 'performance',
            'perf_active_sub'   => $this->current_sub,
            'base'              => $this->base(),
        ];
    }

    private function _employees()
    {
        return $this->CI->db
            ->select("id, CONCAT(first_name,' ',last_name) AS full_name, employee_number, photo, department_id")
            ->where('status', 'Active')
            ->order_by('first_name', 'ASC')
            ->get(db_prefix() . 'hr_employees')->result();
    }

    private function _current_employee()
    {
        $staff = get_staff(get_staff_user_id());
        if (!$staff) return null;
        return $this->CI->db
            ->where('company_email', $staff->email)
            ->or_where('personal_email', $staff->email)
            ->limit(1)
            ->get(db_prefix() . 'hr_employees')->row();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Dashboard
    // ══════════════════════════════════════════════════════════════════════════

    private function _dashboard()
    {
        $stats = $this->perf->get_dashboard_stats();
        $this->CI->load->view('xetuu_hr/admin/performance/dashboard', array_merge(
            $this->_base_data(),
            ['title' => 'Performance Dashboard', 'stats' => $stats]
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Goals
    // ══════════════════════════════════════════════════════════════════════════

    private function _goals($action, $id)
    {
        $base = $this->base();

        // ── AJAX: update goal progress ─────────────────────────────────────
        if ($action === 'update_progress' && $id) {
            header('Content-Type: application/json');
            $emp = $this->_current_employee();
            $goal = $this->perf->get_goal((int)$id);
            if (!$goal) { echo json_encode(['success'=>false,'error'=>'Not found']); exit; }
            $new_val = (float)$this->post('new_value');
            $note    = $this->post('note') ?: '';
            $this->perf->add_goal_update([
                'goal_id'        => (int)$id,
                'updated_by'     => $emp ? $emp->id : 0,
                'previous_value' => $goal->current_value,
                'new_value'      => $new_val,
                'note'           => $note,
            ]);
            $goal = $this->perf->get_goal((int)$id);
            echo json_encode(['success'=>true,'pct'=>$goal->completion_pct,'status'=>$goal->status]);
            exit;
        }

        // ── Delete ─────────────────────────────────────────────────────────
        if ($action === 'delete' && $id) {
            $this->perf->delete_goal((int)$id);
            set_alert('success', 'Goal deleted.');
            redirect($base . '/performance/goals');
        }

        // ── Add / Edit form ────────────────────────────────────────────────
        if ($action === 'add' || ($action === 'edit' && $id)) {
            if ($this->CI->input->server('REQUEST_METHOD') === 'POST') {
                $data = [
                    'employee_id'            => (int)$this->post('employee_id'),
                    'title'                  => $this->post('title'),
                    'description'            => $this->post('description'),
                    'category'               => $this->post('category'),
                    'type'                   => $this->post('type'),
                    'priority'               => $this->post('priority'),
                    'target_value'           => (float)$this->post('target_value'),
                    'unit'                   => $this->post('unit'),
                    'start_date'             => $this->post('start_date') ?: null,
                    'due_date'               => $this->post('due_date') ?: null,
                    'linked_appraisal_cycle' => $this->post('linked_appraisal_cycle') ?: null,
                    'status'                 => $this->post('status') ?: 'Active',
                ];
                $this->perf->save_goal($data, $id ? (int)$id : null);
                set_alert('success', $id ? 'Goal updated.' : 'Goal created.');
                redirect($base . '/performance/goals');
            }
            $goal     = $id ? $this->perf->get_goal((int)$id) : null;
            $this->CI->load->view('xetuu_hr/admin/performance/goal_form', array_merge(
                $this->_base_data(),
                [
                    'title'     => $id ? 'Edit Goal' : 'New Goal',
                    'goal'      => $goal,
                    'employees' => $this->_employees(),
                    'cycles'    => $this->perf->get_cycles(),
                ]
            ));
            return;
        }

        // ── List ───────────────────────────────────────────────────────────
        $filters = [
            'employee_id' => $this->get('employee_id'),
            'status'      => $this->get('status'),
            'category'    => $this->get('category'),
        ];
        $this->CI->load->view('xetuu_hr/admin/performance/goals', array_merge(
            $this->_base_data(),
            [
                'title'     => 'Goals & OKRs',
                'goals'     => $this->perf->get_goals(array_filter($filters)),
                'filters'   => $filters,
                'employees' => $this->_employees(),
                'cycles'    => $this->perf->get_cycles(),
            ]
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Appraisals
    // ══════════════════════════════════════════════════════════════════════════

    private function _appraisals($action, $id)
    {
        $base = $this->base();

        // ── Delete ─────────────────────────────────────────────────────────
        if ($action === 'delete' && $id) {
            $this->perf->delete_appraisal((int)$id);
            set_alert('success', 'Appraisal deleted.');
            redirect($base . '/performance/appraisals');
        }

        // ── Score submit ───────────────────────────────────────────────────
        if ($action === 'score' && $id && $this->CI->input->server('REQUEST_METHOD') === 'POST') {
            $scores_raw = $this->post('scores') ?? [];
            $scores_arr = [];
            foreach ($scores_raw as $cid => $vals) {
                $scores_arr[(int)$cid] = [
                    'self_score'      => isset($vals['self_score'])    ? (float)$vals['self_score']    : null,
                    'manager_score'   => isset($vals['manager_score']) ? (float)$vals['manager_score'] : null,
                    'self_comment'    => $vals['self_comment']    ?? null,
                    'manager_comment' => $vals['manager_comment'] ?? null,
                ];
                // final = manager if set, else self
                $scores_arr[(int)$cid]['final_score'] = $scores_arr[(int)$cid]['manager_score']
                    ?? $scores_arr[(int)$cid]['self_score'];
            }
            $this->perf->upsert_scores((int)$id, $scores_arr);
            $final_score = $this->perf->compute_final_score((int)$id);
            $rating      = $this->perf->score_to_rating($final_score);
            $new_status  = $this->post('submit_final') ? 'Completed' : 'In Progress';
            $this->perf->save_appraisal([
                'score'  => $final_score,
                'rating' => $rating,
                'status' => $new_status,
                'comments' => $this->post('comments'),
            ], (int)$id);
            set_alert('success', $new_status === 'Completed' ? 'Appraisal completed. Score: '.$final_score.'%' : 'Scores saved.');
            redirect($base . '/performance/appraisals/view/' . $id);
        }

        // ── View appraisal ─────────────────────────────────────────────────
        if ($action === 'view' && $id) {
            $appraisal = $this->perf->get_appraisal((int)$id);
            if (!$appraisal) show_404();
            $this->CI->load->view('xetuu_hr/admin/performance/appraisal_view', array_merge(
                $this->_base_data(),
                ['title' => 'Appraisal Detail', 'appraisal' => $appraisal]
            ));
            return;
        }

        // ── Score form ─────────────────────────────────────────────────────
        if ($action === 'score' && $id) {
            $appraisal = $this->perf->get_appraisal((int)$id);
            if (!$appraisal) show_404();
            $criteria = $appraisal->template_id ? $this->perf->get_criteria($appraisal->template_id) : [];
            $this->CI->load->view('xetuu_hr/admin/performance/appraisal_score', array_merge(
                $this->_base_data(),
                ['title' => 'Score Appraisal', 'appraisal' => $appraisal, 'criteria' => $criteria]
            ));
            return;
        }

        // ── Add form ───────────────────────────────────────────────────────
        if ($action === 'add') {
            if ($this->CI->input->server('REQUEST_METHOD') === 'POST') {
                $appraisal_id = $this->perf->save_appraisal([
                    'cycle_id'    => (int)$this->post('cycle_id'),
                    'employee_id' => (int)$this->post('employee_id'),
                    'reviewer_id' => (int)$this->post('reviewer_id') ?: null,
                    'template_id' => (int)$this->post('template_id') ?: null,
                    'status'      => 'Pending',
                ]);
                set_alert('success', 'Appraisal created.');
                redirect($base . '/performance/appraisals/score/' . $appraisal_id);
            }
            $this->CI->load->view('xetuu_hr/admin/performance/appraisal_form', array_merge(
                $this->_base_data(),
                [
                    'title'     => 'New Appraisal',
                    'employees' => $this->_employees(),
                    'cycles'    => $this->perf->get_cycles('Active'),
                    'templates' => $this->perf->get_templates(true),
                ]
            ));
            return;
        }

        // ── List ───────────────────────────────────────────────────────────
        $filters = [
            'cycle_id'    => $this->get('cycle_id'),
            'employee_id' => $this->get('employee_id'),
            'status'      => $this->get('status'),
        ];
        $this->CI->load->view('xetuu_hr/admin/performance/appraisals', array_merge(
            $this->_base_data(),
            [
                'title'      => 'Appraisals',
                'appraisals' => $this->perf->get_appraisals(array_filter($filters)),
                'filters'    => $filters,
                'cycles'     => $this->perf->get_cycles(),
                'employees'  => $this->_employees(),
            ]
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 360° Feedback
    // ══════════════════════════════════════════════════════════════════════════

    private function _feedback($action, $id)
    {
        $base = $this->base();

        // ── AJAX: add reviewer ─────────────────────────────────────────────
        if ($action === 'add_reviewer' && $id) {
            header('Content-Type: application/json');
            $rid = $this->perf->add_reviewer([
                'feedback_id'          => (int)$id,
                'reviewer_type'        => $this->post('reviewer_type'),
                'reviewer_employee_id' => (int)$this->post('reviewer_employee_id') ?: null,
                'reviewer_name'        => $this->post('reviewer_name'),
                'reviewer_email'       => $this->post('reviewer_email'),
            ]);
            echo json_encode(['success' => true, 'id' => $rid]);
            exit;
        }

        // ── AJAX: remove reviewer ──────────────────────────────────────────
        if ($action === 'remove_reviewer' && $id) {
            header('Content-Type: application/json');
            $this->perf->remove_reviewer((int)$id);
            echo json_encode(['success' => true]);
            exit;
        }

        // ── AJAX: add question ─────────────────────────────────────────────
        if ($action === 'add_question' && $id) {
            header('Content-Type: application/json');
            $qid = $this->perf->save_question([
                'feedback_id'   => (int)$id,
                'question'      => $this->post('question'),
                'question_type' => $this->post('question_type') ?: 'rating',
                'sort_order'    => (int)$this->post('sort_order'),
            ]);
            echo json_encode(['success' => true, 'id' => $qid]);
            exit;
        }

        // ── Send / activate ────────────────────────────────────────────────
        if ($action === 'send' && $id) {
            $this->perf->save_feedback(['status' => 'Sent'], (int)$id);
            set_alert('success', 'Feedback requests sent to reviewers.');
            redirect($base . '/performance/feedback/view/' . $id);
        }

        // ── View ───────────────────────────────────────────────────────────
        if ($action === 'view' && $id) {
            $fb = $this->perf->get_feedback((int)$id);
            if (!$fb) show_404();
            $this->CI->load->view('xetuu_hr/admin/performance/feedback_view', array_merge(
                $this->_base_data(),
                ['title' => '360° Feedback', 'fb' => $fb, 'employees' => $this->_employees()]
            ));
            return;
        }

        // ── Delete ─────────────────────────────────────────────────────────
        if ($action === 'delete' && $id) {
            $this->perf->delete_feedback((int)$id);
            set_alert('success', 'Feedback deleted.');
            redirect($base . '/performance/feedback');
        }

        // ── Add ────────────────────────────────────────────────────────────
        if ($action === 'add') {
            if ($this->CI->input->server('REQUEST_METHOD') === 'POST') {
                $fb_id = $this->perf->save_feedback([
                    'appraisee_id' => (int)$this->post('appraisee_id'),
                    'cycle_id'     => (int)$this->post('cycle_id') ?: null,
                    'title'        => $this->post('title'),
                    'anonymous'    => $this->post('anonymous') ? 1 : 0,
                    'deadline'     => $this->post('deadline') ?: null,
                    'status'       => 'Draft',
                ]);
                // seed default questions
                $defaults = [
                    ['question'=>'How effectively does this employee communicate?','question_type'=>'rating','sort_order'=>1],
                    ['question'=>'How well does this employee collaborate with the team?','question_type'=>'rating','sort_order'=>2],
                    ['question'=>'Rate this employee\'s quality of work.','question_type'=>'rating','sort_order'=>3],
                    ['question'=>'Rate this employee\'s initiative and proactivity.','question_type'=>'rating','sort_order'=>4],
                    ['question'=>'What are this employee\'s key strengths?','question_type'=>'text','sort_order'=>5],
                    ['question'=>'What areas should this employee improve?','question_type'=>'text','sort_order'=>6],
                ];
                foreach ($defaults as $dq) {
                    $dq['feedback_id'] = $fb_id;
                    $this->perf->save_question($dq);
                }
                set_alert('success', '360° Feedback created.');
                redirect($base . '/performance/feedback/view/' . $fb_id);
            }
            $this->CI->load->view('xetuu_hr/admin/performance/feedback_form', array_merge(
                $this->_base_data(),
                [
                    'title'     => 'New 360° Feedback',
                    'employees' => $this->_employees(),
                    'cycles'    => $this->perf->get_cycles(),
                ]
            ));
            return;
        }

        // ── List ───────────────────────────────────────────────────────────
        $filters = ['status' => $this->get('status'), 'appraisee_id' => $this->get('appraisee_id')];
        $this->CI->load->view('xetuu_hr/admin/performance/feedback', array_merge(
            $this->_base_data(),
            [
                'title'     => '360° Feedback',
                'feedbacks' => $this->perf->get_feedbacks(array_filter($filters)),
                'filters'   => $filters,
                'employees' => $this->_employees(),
            ]
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Promotions
    // ══════════════════════════════════════════════════════════════════════════

    private function _promotions($action, $id)
    {
        $base = $this->base();
        $p    = db_prefix();

        // ── Apply promotion to employee record ─────────────────────────────
        if ($action === 'apply' && $id) {
            $this->perf->apply_promotion((int)$id);
            set_alert('success', 'Promotion applied to employee record.');
            redirect($base . '/performance/promotions');
        }

        // ── Approve ────────────────────────────────────────────────────────
        if ($action === 'approve' && $id) {
            $emp = $this->_current_employee();
            $this->perf->save_promotion(['status'=>'Approved','approved_by'=>$emp?$emp->id:null], (int)$id);
            set_alert('success', 'Promotion approved.');
            redirect($base . '/performance/promotions');
        }

        // ── Delete ─────────────────────────────────────────────────────────
        if ($action === 'delete' && $id) {
            $this->perf->delete_promotion((int)$id);
            set_alert('success', 'Promotion deleted.');
            redirect($base . '/performance/promotions');
        }

        // ── Add / Edit ─────────────────────────────────────────────────────
        if ($action === 'add' || ($action === 'edit' && $id)) {
            if ($this->CI->input->server('REQUEST_METHOD') === 'POST') {
                $data = [
                    'employee_id'         => (int)$this->post('employee_id'),
                    'from_designation_id' => (int)$this->post('from_designation_id') ?: null,
                    'to_designation_id'   => (int)$this->post('to_designation_id')   ?: null,
                    'from_grade_id'       => (int)$this->post('from_grade_id')        ?: null,
                    'to_grade_id'         => (int)$this->post('to_grade_id')          ?: null,
                    'from_department_id'  => (int)$this->post('from_department_id')   ?: null,
                    'to_department_id'    => (int)$this->post('to_department_id')     ?: null,
                    'effective_date'      => $this->post('effective_date'),
                    'salary_before'       => $this->post('salary_before') ?: null,
                    'salary_after'        => $this->post('salary_after')  ?: null,
                    'reason'              => $this->post('reason'),
                    'status'              => 'Draft',
                ];
                $this->perf->save_promotion($data, $id ? (int)$id : null);
                set_alert('success', $id ? 'Promotion updated.' : 'Promotion record created.');
                redirect($base . '/performance/promotions');
            }
            $promotion    = $id ? $this->perf->get_promotion((int)$id) : null;
            $designations = $this->CI->db->order_by('name','ASC')->get($p.'hr_designations')->result();
            $grades       = $this->CI->db->order_by('name','ASC')->get($p.'hr_employee_grades')->result();
            $departments  = $this->CI->db->order_by('name','ASC')->get($p.'hr_departments')->result();
            $this->CI->load->view('xetuu_hr/admin/performance/promotion_form', array_merge(
                $this->_base_data(),
                [
                    'title'        => $id ? 'Edit Promotion' : 'New Promotion',
                    'promotion'    => $promotion,
                    'employees'    => $this->_employees(),
                    'designations' => $designations,
                    'grades'       => $grades,
                    'departments'  => $departments,
                ]
            ));
            return;
        }

        // ── List ───────────────────────────────────────────────────────────
        $filters = ['status' => $this->get('status'), 'year' => $this->get('year')];
        $this->CI->load->view('xetuu_hr/admin/performance/promotions', array_merge(
            $this->_base_data(),
            [
                'title'      => 'Promotions',
                'promotions' => $this->perf->get_promotions(array_filter($filters)),
                'filters'    => $filters,
                'employees'  => $this->_employees(),
            ]
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Config (Templates & Cycles)
    // ══════════════════════════════════════════════════════════════════════════

    private function _config($action, $id)
    {
        $base = $this->base();

        // ── Templates ──────────────────────────────────────────────────────
        if ($action === 'templates') {
            if ($this->CI->input->server('REQUEST_METHOD') === 'POST') {
                $sub_action = $this->post('sub_action');

                if ($sub_action === 'save_template') {
                    $data = [
                        'name'        => $this->post('name'),
                        'description' => $this->post('description'),
                        'active'      => 1,
                    ];
                    $tpl_id = $this->perf->save_template($data, $id ? (int)$id : null);
                    // Save criteria
                    $names   = $this->post('criteria_name')   ?? [];
                    $cats    = $this->post('criteria_cat')    ?? [];
                    $weights = $this->post('criteria_weight') ?? [];
                    $maxs    = $this->post('criteria_max')    ?? [];
                    $descs   = $this->post('criteria_desc')   ?? [];
                    // Delete existing and re-insert
                    if ($id) $this->CI->db->where('template_id', (int)$id)->delete(db_prefix().'hr_appraisal_template_criteria');
                    foreach ($names as $i => $name) {
                        if (trim($name) === '') continue;
                        $this->perf->save_criterion([
                            'template_id' => $tpl_id,
                            'name'        => $name,
                            'category'    => $cats[$i] ?? '',
                            'weight'      => (float)($weights[$i] ?? 100),
                            'max_score'   => (float)($maxs[$i] ?? 5),
                            'description' => $descs[$i] ?? '',
                            'sort_order'  => $i,
                        ]);
                    }
                    set_alert('success', 'Template saved.');
                    redirect($base . '/performance/config/templates');
                }

                if ($sub_action === 'delete_template' && $id) {
                    $this->perf->delete_template((int)$id);
                    set_alert('success', 'Template deleted.');
                    redirect($base . '/performance/config/templates');
                }
            }

            $edit_tpl = $id ? $this->perf->get_template((int)$id) : null;
            $this->CI->load->view('xetuu_hr/admin/performance/config/templates', array_merge(
                $this->_base_data(),
                [
                    'title'     => 'Appraisal Templates',
                    'templates' => $this->perf->get_templates(),
                    'edit_tpl'  => $edit_tpl,
                ]
            ));
            return;
        }

        // ── Cycles ─────────────────────────────────────────────────────────
        if ($action === 'cycles') {
            if ($this->CI->input->server('REQUEST_METHOD') === 'POST') {
                $sub_action = $this->post('sub_action');
                if ($sub_action === 'save_cycle') {
                    $data = [
                        'name'       => $this->post('name'),
                        'start_date' => $this->post('start_date'),
                        'end_date'   => $this->post('end_date'),
                        'status'     => $this->post('status') ?: 'Draft',
                    ];
                    $this->perf->save_cycle($data, $id ? (int)$id : null);
                    set_alert('success', 'Cycle saved.');
                }
                if ($sub_action === 'delete_cycle' && $id) {
                    $this->perf->delete_cycle((int)$id);
                    set_alert('success', 'Cycle deleted.');
                }
                redirect($base . '/performance/config/cycles');
            }

            $edit_cycle = $id ? $this->perf->get_cycle((int)$id) : null;
            $this->CI->load->view('xetuu_hr/admin/performance/config/cycles', array_merge(
                $this->_base_data(),
                [
                    'title'      => 'Appraisal Cycles',
                    'cycles'     => $this->perf->get_cycles(),
                    'edit_cycle' => $edit_cycle,
                ]
            ));
            return;
        }

        $this->redirect($base . '/performance/config/cycles');
    }
}
