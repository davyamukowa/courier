<?php defined('BASEPATH') or exit('No direct script access allowed');

class Xr_performance_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function p() { return db_prefix(); }

    // ══════════════════════════════════════════════════════════════════════════
    // Appraisal Cycles
    // ══════════════════════════════════════════════════════════════════════════

    public function get_cycles($status = null)
    {
        if ($status) $this->db->where('status', $status);
        return $this->db->order_by('start_date', 'DESC')
            ->get($this->p() . 'hr_appraisal_cycles')->result();
    }

    public function get_cycle($id)
    {
        return $this->db->where('id', $id)
            ->get($this->p() . 'hr_appraisal_cycles')->row();
    }

    public function save_cycle($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_appraisal_cycles', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_appraisal_cycles', $data);
        return $this->db->insert_id();
    }

    public function delete_cycle($id)
    {
        $this->db->where('id', $id)->delete($this->p() . 'hr_appraisal_cycles');
        return $this->db->affected_rows() > 0;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Appraisal Templates
    // ══════════════════════════════════════════════════════════════════════════

    public function get_templates($active_only = false)
    {
        if ($active_only) $this->db->where('active', 1);
        return $this->db->order_by('name', 'ASC')
            ->get($this->p() . 'hr_appraisal_templates')->result();
    }

    public function get_template($id)
    {
        $tpl = $this->db->where('id', $id)
            ->get($this->p() . 'hr_appraisal_templates')->row();
        if (!$tpl) return null;
        $tpl->criteria = $this->get_criteria($id);
        return $tpl;
    }

    public function save_template($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_appraisal_templates', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_appraisal_templates', $data);
        return $this->db->insert_id();
    }

    public function delete_template($id)
    {
        $this->db->where('template_id', $id)->delete($this->p() . 'hr_appraisal_template_criteria');
        $this->db->where('id', $id)->delete($this->p() . 'hr_appraisal_templates');
        return $this->db->affected_rows() > 0;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Template Criteria
    // ══════════════════════════════════════════════════════════════════════════

    public function get_criteria($template_id)
    {
        return $this->db->where('template_id', $template_id)
            ->order_by('sort_order', 'ASC')
            ->get($this->p() . 'hr_appraisal_template_criteria')->result();
    }

    public function save_criterion($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_appraisal_template_criteria', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_appraisal_template_criteria', $data);
        return $this->db->insert_id();
    }

    public function delete_criterion($id)
    {
        $this->db->where('id', $id)->delete($this->p() . 'hr_appraisal_template_criteria');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Appraisals
    // ══════════════════════════════════════════════════════════════════════════

    public function get_appraisals($filters = [])
    {
        $p = $this->p();
        $q = $this->db
            ->select("a.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                      CONCAT(r.first_name,' ',r.last_name) AS reviewer_name,
                      c.name AS cycle_name, c.start_date AS cycle_start, c.end_date AS cycle_end,
                      t.name AS template_name,
                      e.employee_number, e.photo")
            ->from($p . 'hr_appraisals a')
            ->join($p . 'hr_employees e', 'e.id = a.employee_id', 'left')
            ->join($p . 'hr_employees r', 'r.id = a.reviewer_id', 'left')
            ->join($p . 'hr_appraisal_cycles c', 'c.id = a.cycle_id', 'left')
            ->join($p . 'hr_appraisal_templates t', 't.id = a.template_id', 'left');

        if (!empty($filters['cycle_id']))     $q->where('a.cycle_id', $filters['cycle_id']);
        if (!empty($filters['employee_id']))  $q->where('a.employee_id', $filters['employee_id']);
        if (!empty($filters['status']))       $q->where('a.status', $filters['status']);
        if (!empty($filters['reviewer_id']))  $q->where('a.reviewer_id', $filters['reviewer_id']);

        return $q->order_by('a.date_created', 'DESC')->get()->result();
    }

    public function get_appraisal($id)
    {
        $p = $this->p();
        $row = $this->db
            ->select("a.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                      CONCAT(r.first_name,' ',r.last_name) AS reviewer_name,
                      c.name AS cycle_name, c.start_date AS cycle_start, c.end_date AS cycle_end,
                      t.name AS template_name, e.photo, e.employee_number,
                      d.name AS department_name, des.name AS designation_name")
            ->from($p . 'hr_appraisals a')
            ->join($p . 'hr_employees e', 'e.id = a.employee_id', 'left')
            ->join($p . 'hr_employees r', 'r.id = a.reviewer_id', 'left')
            ->join($p . 'hr_appraisal_cycles c', 'c.id = a.cycle_id', 'left')
            ->join($p . 'hr_appraisal_templates t', 't.id = a.template_id', 'left')
            ->join($p . 'hr_departments d', 'd.id = e.department_id', 'left')
            ->join($p . 'hr_designations des', 'des.id = e.designation_id', 'left')
            ->where('a.id', $id)
            ->get()->row();
        if (!$row) return null;
        $row->scores = $this->get_appraisal_scores($id);
        return $row;
    }

    public function save_appraisal($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_appraisals', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_appraisals', $data);
        return $this->db->insert_id();
    }

    public function delete_appraisal($id)
    {
        $this->db->where('appraisal_id', $id)->delete($this->p() . 'hr_appraisal_scores');
        $this->db->where('id', $id)->delete($this->p() . 'hr_appraisals');
    }

    // ── Appraisal Scores ──────────────────────────────────────────────────────

    public function get_appraisal_scores($appraisal_id)
    {
        $p = $this->p();
        return $this->db
            ->select('s.*, c.name AS criteria_name, c.description AS criteria_description,
                      c.category, c.weight, c.max_score, c.sort_order')
            ->from($p . 'hr_appraisal_scores s')
            ->join($p . 'hr_appraisal_template_criteria c', 'c.id = s.criteria_id', 'left')
            ->where('s.appraisal_id', $appraisal_id)
            ->order_by('c.sort_order', 'ASC')
            ->get()->result();
    }

    public function save_score($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_appraisal_scores', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_appraisal_scores', $data);
        return $this->db->insert_id();
    }

    public function upsert_scores($appraisal_id, $scores_array)
    {
        foreach ($scores_array as $criteria_id => $vals) {
            $existing = $this->db->where('appraisal_id', $appraisal_id)
                ->where('criteria_id', $criteria_id)
                ->get($this->p() . 'hr_appraisal_scores')->row();
            $data = array_merge(['appraisal_id' => $appraisal_id, 'criteria_id' => $criteria_id], $vals);
            if ($existing) {
                $this->db->where('id', $existing->id)->update($this->p() . 'hr_appraisal_scores', $data);
            } else {
                $data['date_created'] = date('Y-m-d H:i:s');
                $this->db->insert($this->p() . 'hr_appraisal_scores', $data);
            }
        }
    }

    public function compute_final_score($appraisal_id)
    {
        $scores = $this->get_appraisal_scores($appraisal_id);
        $total_weight = 0; $weighted_sum = 0;
        foreach ($scores as $s) {
            $final = ($s->final_score !== null) ? $s->final_score : ($s->manager_score ?? $s->self_score ?? 0);
            $pct   = ($s->max_score > 0) ? ($final / $s->max_score) : 0;
            $weighted_sum  += $pct * $s->weight;
            $total_weight  += $s->weight;
        }
        if ($total_weight <= 0) return 0;
        return round(($weighted_sum / $total_weight) * 100, 2);
    }

    public function score_to_rating($score)
    {
        if ($score >= 90) return 'Outstanding';
        if ($score >= 75) return 'Exceeds Expectations';
        if ($score >= 60) return 'Meets Expectations';
        if ($score >= 40) return 'Below Expectations';
        return 'Unsatisfactory';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Goals
    // ══════════════════════════════════════════════════════════════════════════

    public function get_goals($filters = [])
    {
        $p = $this->p();
        $q = $this->db
            ->select("g.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                      e.photo, e.employee_number, d.name AS department_name")
            ->from($p . 'hr_goals g')
            ->join($p . 'hr_employees e', 'e.id = g.employee_id', 'left')
            ->join($p . 'hr_departments d', 'd.id = e.department_id', 'left');

        if (!empty($filters['employee_id'])) $q->where('g.employee_id', $filters['employee_id']);
        if (!empty($filters['status']))      $q->where('g.status', $filters['status']);
        if (!empty($filters['category']))    $q->where('g.category', $filters['category']);
        if (!empty($filters['cycle_id']))    $q->where('g.linked_appraisal_cycle', $filters['cycle_id']);

        return $q->order_by('g.due_date', 'ASC')->order_by('g.priority', 'DESC')->get()->result();
    }

    public function get_goal($id)
    {
        $p = $this->p();
        $row = $this->db
            ->select("g.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name, e.photo")
            ->from($p . 'hr_goals g')
            ->join($p . 'hr_employees e', 'e.id = g.employee_id', 'left')
            ->where('g.id', $id)->get()->row();
        if (!$row) return null;
        $row->updates = $this->get_goal_updates($id);
        return $row;
    }

    public function save_goal($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        $data['date_modified'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_goals', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_goals', $data);
        return $this->db->insert_id();
    }

    public function delete_goal($id)
    {
        $this->db->where('goal_id', $id)->delete($this->p() . 'hr_goal_updates');
        $this->db->where('id', $id)->delete($this->p() . 'hr_goals');
    }

    public function get_goal_updates($goal_id)
    {
        $p = $this->p();
        return $this->db
            ->select("u.*, CONCAT(e.first_name,' ',e.last_name) AS updater_name")
            ->from($p . 'hr_goal_updates u')
            ->join($p . 'hr_employees e', 'e.id = u.updated_by', 'left')
            ->where('u.goal_id', $goal_id)
            ->order_by('u.date_created', 'DESC')->get()->result();
    }

    public function add_goal_update($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_goal_updates', $data);
        // recalculate completion pct
        $goal = $this->db->where('id', $data['goal_id'])->get($this->p() . 'hr_goals')->row();
        if ($goal && $goal->target_value > 0) {
            $pct = min(100, round(($data['new_value'] / $goal->target_value) * 100, 2));
            $status = ($pct >= 100) ? 'Completed' : $goal->status;
            $this->db->where('id', $goal->id)->update($this->p() . 'hr_goals', [
                'current_value'  => $data['new_value'],
                'completion_pct' => $pct,
                'status'         => $status,
                'date_modified'  => date('Y-m-d H:i:s'),
            ]);
        }
        return $this->db->insert_id();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 360° Feedback
    // ══════════════════════════════════════════════════════════════════════════

    public function get_feedbacks($filters = [])
    {
        $p = $this->p();
        $q = $this->db
            ->select("f.*, CONCAT(e.first_name,' ',e.last_name) AS appraisee_name,
                      e.photo,
                      (SELECT COUNT(*) FROM {$p}hr_feedback_reviewers WHERE feedback_id=f.id) AS reviewer_count,
                      (SELECT COUNT(*) FROM {$p}hr_feedback_reviewers WHERE feedback_id=f.id AND submitted=1) AS submitted_count")
            ->from($p . 'hr_feedback_360 f')
            ->join($p . 'hr_employees e', 'e.id = f.appraisee_id', 'left');

        if (!empty($filters['appraisee_id'])) $q->where('f.appraisee_id', $filters['appraisee_id']);
        if (!empty($filters['status']))       $q->where('f.status', $filters['status']);

        return $q->order_by('f.date_created', 'DESC')->get()->result();
    }

    public function get_feedback($id)
    {
        $p = $this->p();
        $row = $this->db
            ->select("f.*, CONCAT(e.first_name,' ',e.last_name) AS appraisee_name, e.photo")
            ->from($p . 'hr_feedback_360 f')
            ->join($p . 'hr_employees e', 'e.id = f.appraisee_id', 'left')
            ->where('f.id', $id)->get()->row();
        if (!$row) return null;
        $row->reviewers  = $this->get_reviewers($id);
        $row->questions  = $this->get_questions($id);
        return $row;
    }

    public function save_feedback($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_feedback_360', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_feedback_360', $data);
        return $this->db->insert_id();
    }

    public function delete_feedback($id)
    {
        $this->db->where('feedback_id', $id)->delete($this->p() . 'hr_feedback_reviewers');
        $this->db->where('feedback_id', $id)->delete($this->p() . 'hr_feedback_questions');
        $this->db->where('feedback_id', $id)->delete($this->p() . 'hr_feedback_responses');
        $this->db->where('id', $id)->delete($this->p() . 'hr_feedback_360');
    }

    public function get_reviewers($feedback_id)
    {
        $p = $this->p();
        return $this->db
            ->select("r.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name, e.photo")
            ->from($p . 'hr_feedback_reviewers r')
            ->join($p . 'hr_employees e', 'e.id = r.reviewer_employee_id', 'left')
            ->where('r.feedback_id', $feedback_id)
            ->get()->result();
    }

    public function add_reviewer($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        if (empty($data['token'])) $data['token'] = bin2hex(random_bytes(24));
        $this->db->insert($this->p() . 'hr_feedback_reviewers', $data);
        return $this->db->insert_id();
    }

    public function remove_reviewer($id)
    {
        $this->db->where('id', $id)->delete($this->p() . 'hr_feedback_reviewers');
    }

    public function get_questions($feedback_id)
    {
        return $this->db->where('feedback_id', $feedback_id)
            ->order_by('sort_order', 'ASC')
            ->get($this->p() . 'hr_feedback_questions')->result();
    }

    public function save_question($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_feedback_questions', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_feedback_questions', $data);
        return $this->db->insert_id();
    }

    public function save_response($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_feedback_responses', $data);
    }

    public function get_responses($feedback_id, $reviewer_id = null)
    {
        $q = $this->db->where('feedback_id', $feedback_id);
        if ($reviewer_id) $q->where('reviewer_id', $reviewer_id);
        return $q->get($this->p() . 'hr_feedback_responses')->result();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Promotions
    // ══════════════════════════════════════════════════════════════════════════

    public function get_promotions($filters = [])
    {
        $p = $this->p();
        $q = $this->db
            ->select("pr.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                      e.photo, e.employee_number,
                      fd.name AS from_designation, td.name AS to_designation,
                      fg.name AS from_grade, tg.name AS to_grade,
                      fdept.name AS from_department, tdept.name AS to_department,
                      CONCAT(ab.first_name,' ',ab.last_name) AS approved_by_name")
            ->from($p . 'hr_promotions pr')
            ->join($p . 'hr_employees e',      'e.id = pr.employee_id', 'left')
            ->join($p . 'hr_designations fd',  'fd.id = pr.from_designation_id', 'left')
            ->join($p . 'hr_designations td',  'td.id = pr.to_designation_id', 'left')
            ->join($p . 'hr_employee_grades fg','fg.id = pr.from_grade_id', 'left')
            ->join($p . 'hr_employee_grades tg','tg.id = pr.to_grade_id', 'left')
            ->join($p . 'hr_departments fdept','fdept.id = pr.from_department_id', 'left')
            ->join($p . 'hr_departments tdept','tdept.id = pr.to_department_id', 'left')
            ->join($p . 'hr_employees ab',     'ab.id = pr.approved_by', 'left');

        if (!empty($filters['employee_id'])) $q->where('pr.employee_id', $filters['employee_id']);
        if (!empty($filters['status']))      $q->where('pr.status', $filters['status']);
        if (!empty($filters['year']))        $q->where('YEAR(pr.effective_date)', $filters['year']);

        return $q->order_by('pr.effective_date', 'DESC')->get()->result();
    }

    public function get_promotion($id)
    {
        $p = $this->p();
        return $this->db
            ->select("pr.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                      e.photo, e.employee_number,
                      fd.name AS from_designation, td.name AS to_designation,
                      fg.name AS from_grade, tg.name AS to_grade,
                      fdept.name AS from_department, tdept.name AS to_department")
            ->from($p . 'hr_promotions pr')
            ->join($p . 'hr_employees e',      'e.id = pr.employee_id', 'left')
            ->join($p . 'hr_designations fd',  'fd.id = pr.from_designation_id', 'left')
            ->join($p . 'hr_designations td',  'td.id = pr.to_designation_id', 'left')
            ->join($p . 'hr_employee_grades fg','fg.id = pr.from_grade_id', 'left')
            ->join($p . 'hr_employee_grades tg','tg.id = pr.to_grade_id', 'left')
            ->join($p . 'hr_departments fdept','fdept.id = pr.from_department_id', 'left')
            ->join($p . 'hr_departments tdept','tdept.id = pr.to_department_id', 'left')
            ->where('pr.id', $id)->get()->row();
    }

    public function save_promotion($data, $id = null)
    {
        if (!isset($data['date_created'])) $data['date_created'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update($this->p() . 'hr_promotions', $data);
            return $id;
        }
        $this->db->insert($this->p() . 'hr_promotions', $data);
        return $this->db->insert_id();
    }

    public function apply_promotion($id)
    {
        $pr = $this->get_promotion($id);
        if (!$pr) return false;
        $updates = ['status' => 'Applied'];
        if ($pr->to_designation_id) $updates['designation_id'] = $pr->to_designation_id;
        if ($pr->to_grade_id)       $updates['grade_id']       = $pr->to_grade_id;
        if ($pr->to_department_id)  $updates['department_id']  = $pr->to_department_id;
        $this->db->where('id', $pr->employee_id)->update($this->p() . 'hr_employees', $updates);
        $this->db->where('id', $id)->update($this->p() . 'hr_promotions', ['status' => 'Applied']);
        return true;
    }

    public function delete_promotion($id)
    {
        $this->db->where('id', $id)->delete($this->p() . 'hr_promotions');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Dashboard Stats
    // ══════════════════════════════════════════════════════════════════════════

    public function get_dashboard_stats()
    {
        $p = $this->p();
        $cycle = $this->db->where('status', 'Active')->order_by('start_date','DESC')
            ->limit(1)->get($p . 'hr_appraisal_cycles')->row();

        $stats = [
            'active_cycle'        => $cycle,
            'total_appraisals'    => $this->db->where($cycle ? 'cycle_id' : '1', $cycle ? $cycle->id : 1)->count_all_results($p . 'hr_appraisals'),
            'pending_appraisals'  => $this->db->where('status', 'Pending')->count_all_results($p . 'hr_appraisals'),
            'completed_appraisals'=> $this->db->where('status', 'Completed')->count_all_results($p . 'hr_appraisals'),
            'active_goals'        => $this->db->where('status', 'Active')->count_all_results($p . 'hr_goals'),
            'completed_goals'     => $this->db->where('status', 'Completed')->count_all_results($p . 'hr_goals'),
            'overdue_goals'       => $this->db->where('status', 'Active')->where('due_date <', date('Y-m-d'))->count_all_results($p . 'hr_goals'),
            'feedback_pending'    => $this->db->where('status', 'Sent')->count_all_results($p . 'hr_feedback_360'),
            'promotions_pending'  => $this->db->where('status', 'Approved')->count_all_results($p . 'hr_promotions'),
        ];

        // recent appraisals
        $stats['recent_appraisals'] = $this->get_appraisals([]);
        if (count($stats['recent_appraisals']) > 6) {
            $stats['recent_appraisals'] = array_slice($stats['recent_appraisals'], 0, 6);
        }

        // rating distribution for completed appraisals
        $dist = $this->db->select('rating, COUNT(*) as cnt')
            ->where('status', 'Completed')->where('rating IS NOT NULL')
            ->group_by('rating')
            ->get($p . 'hr_appraisals')->result();
        $stats['rating_dist'] = [];
        foreach ($dist as $d) $stats['rating_dist'][$d->rating] = (int)$d->cnt;

        return $stats;
    }
}
