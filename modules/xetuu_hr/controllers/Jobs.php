<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Public-facing careers / job board.
 * Extends ClientsController so the portal navbar (Knowledge Base / Careers / Login)
 * is automatically included via layout(true). No client login is required.
 */
class Jobs extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('xetuu_hr/Xetuu_hr_model', 'hr');
    }

    /** GET xetuu_hr/jobs — list all open + published openings */
    public function index()
    {
        $p = $this->hr->p();
        $this->db->select('jo.*, d.name AS department_name, des.name AS designation_name');
        $this->db->from($p . 'hr_job_openings jo');
        $this->db->join($p . 'hr_departments d',    'd.id = jo.department_id',    'left');
        $this->db->join($p . 'hr_designations des', 'des.id = jo.designation_id', 'left');
        $this->db->where('jo.status', 'Open');
        $this->db->where('jo.publish_on_website', 1);
        $this->db->order_by('jo.id', 'DESC');

        $data = [
            'openings' => $this->db->get()->result(),
            'title'    => 'Careers',
        ];

        $this->data($data);
        $this->view('xetuu_hr/public/jobs');
        $this->layout(true);
    }

    /** GET xetuu_hr/jobs/detail/{id} — single job + apply form */
    public function detail($id = null)
    {
        if (!$id) { show_404(); return; }

        $p = $this->hr->p();
        $this->db->select('jo.*, d.name AS department_name, des.name AS designation_name');
        $this->db->from($p . 'hr_job_openings jo');
        $this->db->join($p . 'hr_departments d',    'd.id = jo.department_id',    'left');
        $this->db->join($p . 'hr_designations des', 'des.id = jo.designation_id', 'left');
        $this->db->where('jo.id', (int)$id);
        $opening = $this->db->get()->row();

        if (!$opening || $opening->status !== 'Open' || empty($opening->publish_on_website)) {
            show_404(); return;
        }

        $data = [
            'opening' => $opening,
            'title'   => $opening->title . ' — Careers',
        ];

        $this->data($data);
        $this->view('xetuu_hr/public/job_detail');
        $this->layout(true);
    }

    /** POST xetuu_hr/jobs/apply — submit application */
    public function apply()
    {
        if (!$this->input->post()) {
            redirect(site_url('xetuu_hr/jobs'));
            return;
        }

        $job_opening_id = (int)$this->input->post('job_opening_id', true);
        $first_name     = $this->input->post('first_name', true);
        $last_name      = $this->input->post('last_name', true);
        $email          = $this->input->post('email', true);
        $phone          = $this->input->post('phone', true);

        if (!$job_opening_id || !$first_name || !$email) {
            set_alert('danger', 'Please fill in all required fields.');
            redirect(site_url('xetuu_hr/jobs/detail/' . $job_opening_id));
            return;
        }

        // Optional resume upload
        $resume_filename = null;
        if (!empty($_FILES['resume']['name'])) {
            $upload_path = FCPATH . 'uploads/xetuu_hr/resumes/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $this->load->library('upload', [
                'upload_path'   => $upload_path,
                'allowed_types' => 'pdf|doc|docx',
                'max_size'      => 5120,
                'encrypt_name'  => true,
            ]);

            if ($this->upload->do_upload('resume')) {
                $resume_filename = $this->upload->data('file_name');
            } else {
                set_alert('danger', 'Resume upload failed: ' . $this->upload->display_errors('', ''));
                redirect(site_url('xetuu_hr/jobs/detail/' . $job_opening_id));
                return;
            }
        }

        $this->db->insert($this->hr->p() . 'hr_applicants', [
            'job_opening_id' => $job_opening_id,
            'first_name'     => $first_name,
            'last_name'      => $last_name,
            'email'          => $email,
            'phone'          => $phone,
            'resume'         => $resume_filename,
            'source'         => 'Website',
            'source_name'    => 'Careers Portal',
            'stage'          => 'Applied',
            'date_created'   => date('Y-m-d H:i:s'),
        ]);

        set_alert('success', 'Your application has been submitted! We will be in touch.');
        redirect(site_url('xetuu_hr/jobs/detail/' . $job_opening_id));
    }

    /** GET / POST xetuu_hr/jobs/sign_letter/{hash} — view & sign appointment letter */
    public function sign_letter($hash = null)
    {
        if (!$hash) {
            show_404();
            return;
        }

        $p = $this->hr->p();
        $this->db->select('al.*, CONCAT(a.first_name," ",a.last_name) AS applicant_name, a.email AS applicant_email, a.id AS applicant_id');
        $this->db->from($p . 'hr_appointment_letters al');
        $this->db->join($p . 'hr_applicants a', 'al.applicant_id = a.id', 'left');
        $this->db->where('al.hash', $hash);
        $letter = $this->db->get()->row();

        if (!$letter) {
            show_404();
            return;
        }

        // Fetch terms rows
        $letter_terms = $this->db->where('letter_id', $letter->id)->order_by('sort_order')->get($p . 'hr_appointment_letter_terms')->result();

        if ($this->input->post()) {
            $sig = $this->input->post('applicant_signature');
            if ($sig) {
                // Update letter status to Signed and store signature
                $this->db->where('id', $letter->id)->update($p . 'hr_appointment_letters', [
                    'applicant_signature' => $sig,
                    'status' => 'Signed'
                ]);

                // Trigger Hired & Onboarding workflows
                $this->db->where('id', $letter->applicant_id)->update($p . 'hr_applicants', ['stage' => 'Hired']);
                $this->_hire_applicant($letter->applicant_id);
                $this->_send_applicant_stage_email($letter->applicant_id, 'Hired');

                set_alert('success', 'Thank you! You have successfully signed the appointment letter.');
                redirect(site_url('xetuu_hr/jobs/sign_letter/' . $hash));
                return;
            }
        }

        $data = [
            'letter' => $letter,
            'letter_terms' => $letter_terms,
            'title' => 'Sign Appointment Letter — ' . $letter->letter_number,
        ];

        $this->data($data);
        $this->view('xetuu_hr/public/sign_letter');
        $this->layout(true);
    }

    private function _hire_applicant($applicant_id)
    {
        $p = $this->hr->p();
        $applicant = $this->db->where('id', (int)$applicant_id)->get($p . 'hr_applicants')->row();
        if (!$applicant) {
            return false;
        }

        // Check if employee already exists
        $exists = $this->db->where('personal_email', $applicant->email)->count_all_results($p . 'hr_employees');
        if ($exists) {
            return false;
        }

        // Fetch designation / department from latest offer or job opening
        $offer   = $this->db->where('applicant_id', $applicant_id)->order_by('id','DESC')->get($p . 'hr_job_offers')->row();
        $opening = ($offer && $offer->job_opening_id)
            ? $this->db->where('id', $offer->job_opening_id)->get($p . 'hr_job_openings')->row()
            : ($applicant->job_opening_id ? $this->db->where('id', $applicant->job_opening_id)->get($p . 'hr_job_openings')->row() : null);

        $emp_data = [
            'first_name'     => $applicant->first_name,
            'last_name'      => $applicant->last_name,
            'personal_email' => $applicant->email,
            'email'          => $applicant->email,
            'mobile'         => $applicant->phone,
            'phone'          => $applicant->phone,
            'biography'      => $applicant->cover_letter,
            'status'         => 'Active',
        ];

        if ($offer) {
            $emp_data['designation_id'] = $offer->designation_id;
            $emp_data['date_of_joining'] = $offer->joining_date;
        }
        if ($opening) {
            $emp_data['company_id'] = $opening->company_id;
            $emp_data['department_id'] = $opening->department_id;
            if (empty($emp_data['designation_id'])) {
                $emp_data['designation_id'] = $opening->designation_id;
            }
        }

        $new_emp_id = $this->hr->add_employee($emp_data);
        if (!$new_emp_id) {
            return false;
        }

        // Copy CV/Resume if exists
        if (!empty($applicant->resume)) {
            $src = FCPATH . 'uploads/xetuu_hr/resumes/' . $applicant->resume;
            if (file_exists($src)) {
                $dest_dir = FCPATH . 'uploads/hr_employees/';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0755, true);
                }
                $dest_filename = uniqid('resume_', true) . '_' . $applicant->resume;
                $dst = $dest_dir . $dest_filename;
                if (copy($src, $dst)) {
                    $this->db->insert(db_prefix() . 'files', [
                        'rel_id'    => $new_emp_id,
                        'rel_type'  => 'hr_employee',
                        'file_name' => $dest_filename,
                        'filetype'  => mime_content_type($dst),
                        'dateadded' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        return $new_emp_id;
    }

    private function _send_applicant_stage_email($applicant_id, $stage)
    {
        $p = $this->hr->p();
        $applicant = $this->db->where('id', (int)$applicant_id)->get($p . 'hr_applicants')->row();
        if (!$applicant || empty($applicant->email)) {
            return false;
        }

        $opening = null;
        if ($applicant->job_opening_id) {
            $opening = $this->db->where('id', (int)$applicant->job_opening_id)->get($p . 'hr_job_openings')->row();
        }
        $job_title = $opening ? $opening->title : 'Job Opening';

        $subject = "Application Status Update: " . $job_title . " - " . $stage;
        
        if ($stage === 'Hired') {
            $subject = "Congratulations! You have been hired for the " . $job_title . " position";
            $message = "We are thrilled to inform you that you have been hired for the position of <strong>" . htmlspecialchars($job_title) . "</strong>! Our HR team will contact you shortly to discuss the next steps and onboarding.";
        } else {
            $message = "We wanted to let you know that your application for the <strong>" . htmlspecialchars($job_title) . "</strong> position has progressed to the next stage: <strong>" . htmlspecialchars($stage) . "</strong>. We will be in touch with you regarding the next steps.";
        }

        $body = '
        <div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; padding: 40px 20px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-collapse: collapse; overflow: hidden;">
                <tr>
                    <td style="background-color: #16a34a; padding: 30px 40px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em;">Application Status Update</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 40px; color: #374151; font-size: 15px; line-height: 1.6;">
                        <p style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600; color: #111827;">Dear ' . htmlspecialchars($applicant->first_name . ' ' . $applicant->last_name) . ',</p>
                        <p style="margin: 0 0 24px 0;">' . $message . '</p>
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">Best regards,<br><strong>Tagrit Kenya HR Team</strong></p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af;">
                        This email was sent automatically by Tagrit Kenya Careers.
                    </td>
                </tr>
            </table>
        </div>
        ';

        $this->load->model('emails_model');
        return $this->emails_model->send_simple_email($applicant->email, $subject, $body);
    }
}
