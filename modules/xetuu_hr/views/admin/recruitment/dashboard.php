<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $xhr_active = 'recruitment'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- Sub-action header -->
<div class="xhr-action-header" style="justify-content: space-between; padding: 1.5rem 2rem; background: transparent; border-bottom: none;">
    <div>
        <h2 style="margin: 0 0 0.5rem 0; font-size: 24px; font-weight: 700; color: #1e293b;">Recruitment Dashboard</h2>
        <p style="margin: 0; color: #64748b; font-size: 14px;">Overview of your current hiring funnel and active vacancies.</p>
    </div>
    <div class="xhr-action-buttons" style="display: flex; gap: 12px;">
        <button class="xhr-btn xhr-btn--outline">
            <span class="material-symbols-outlined">calendar_today</span>
            Last 30 Days
            <span class="material-symbols-outlined" style="margin-left: 4px;">expand_more</span>
        </button>
        <button class="xhr-btn xhr-btn--outline">
            Download Report
        </button>
    </div>
</div>

<!-- Page Content -->
<div class="xhr-content" style="padding: 0 2rem 2rem 2rem;">

    <!-- Stat Cards Row -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Card 1 -->
        <div class="xhr-card" style="padding: 1.5rem; position: relative; overflow: hidden; border-left: 4px solid #16a34a;">
            <p style="font-size: 10px; font-weight: 700; color: #16a34a; text-transform: uppercase; margin-bottom: 0.5rem;">Active Openings</p>
            <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 0.25rem;">
                <span style="font-size: 32px; font-weight: 700; color: #0f172a;"><?php echo $stats['open_positions'] ?? 0; ?></span>
            </div>
            <p style="margin: 0; color: #64748b; font-size: 13px;">Across <?php echo $stats['dept_count'] ?? 0; ?> departments</p>
            <span class="material-symbols-outlined" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; color: #f8fafc; pointer-events: none; z-index: 0;">work</span>
        </div>
        <!-- Card 2 -->
        <div class="xhr-card" style="padding: 1.5rem; position: relative; overflow: hidden; border-left: 4px solid #2563eb;">
            <p style="font-size: 10px; font-weight: 700; color: #2563eb; text-transform: uppercase; margin-bottom: 0.5rem;">Total Applicants</p>
            <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 0.25rem;">
                <span style="font-size: 32px; font-weight: 700; color: #0f172a;"><?php echo number_format($stats['applicants'] ?? 0); ?></span>
            </div>
            <p style="margin: 0; color: #64748b; font-size: 13px;">In active pipeline</p>
            <span class="material-symbols-outlined" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; color: #f8fafc; pointer-events: none; z-index: 0;">group_add</span>
        </div>
        <!-- Card 3 -->
        <div class="xhr-card" style="padding: 1.5rem; position: relative; overflow: hidden; border-left: 4px solid #475569;">
            <p style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.5rem;">Avg. Time to Hire</p>
            <div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 0.25rem;">
                <span style="font-size: 32px; font-weight: 700; color: #0f172a;"><?php echo $stats['avg_time_to_hire'] ?? 15; ?></span>
                <span style="font-size: 16px; font-weight: 500; color: #64748b;">days</span>
            </div>
            <p style="margin: 0; color: #64748b; font-size: 13px;">Target: 21 days</p>
            <span class="material-symbols-outlined" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; color: #f8fafc; pointer-events: none; z-index: 0;">update</span>
        </div>
        <!-- Card 4 -->
        <div class="xhr-card" style="padding: 1.5rem; position: relative; overflow: hidden; border-left: 4px solid #ef4444;">
            <p style="font-size: 10px; font-weight: 700; color: #ef4444; text-transform: uppercase; margin-bottom: 0.5rem;">Offer Decline Rate</p>
            <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 0.25rem;">
                <span style="font-size: 32px; font-weight: 700; color: #0f172a;"><?php echo $stats['offer_decline_rate'] ?? 0.0; ?>%</span>
            </div>
            <p style="margin: 0; color: #64748b; font-size: 13px;">Hiring efficiency ratio</p>
            <span class="material-symbols-outlined" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; color: #fef2f2; pointer-events: none; z-index: 0;">cancel</span>
        </div>
    </div>

    <!-- Main Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Recruitment Funnel -->
            <div class="xhr-card" style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #0f172a;">Recruitment Funnel</h3>
                    <a href="#" style="color: #16a34a; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center;">Details <span class="material-symbols-outlined" style="font-size: 16px; margin-left: 4px;">arrow_forward</span></a>
                </div>
                
                <?php
                $sourced_count = $stats['funnel_sourced'] ?? 0;
                $screened_count = $stats['funnel_screened'] ?? 0;
                $interviewed_count = $stats['funnel_interviewed'] ?? 0;
                $offered_count = $stats['funnel_offered'] ?? 0;
                $hired_count = $stats['funnel_hired'] ?? 0;

                $screened_pct = $sourced_count > 0 ? round(($screened_count / $sourced_count) * 100) : 0;
                $interviewed_pct = $sourced_count > 0 ? round(($interviewed_count / $sourced_count) * 100) : 0;
                $offered_pct = $sourced_count > 0 ? round(($offered_count / $sourced_count) * 100) : 0;
                ?>
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <!-- Sourced -->
                    <div style="display: flex; align-items: center; width: 100%; max-width: 600px; margin-bottom: 12px;">
                        <div style="width: 120px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right; padding-right: 20px;">Sourced</div>
                        <div style="flex-grow: 1; height: 12px; background: #16a34a; border-radius: 6px;"></div>
                        <div style="width: 60px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right;"><?php echo $sourced_count; ?></div>
                    </div>
                    <!-- Connector line -->
                    <div style="height: 20px; width: 1px; background: #e2e8f0; margin-bottom: 12px;"></div>
                    
                    <!-- Screened -->
                    <div style="display: flex; align-items: center; width: 100%; max-width: 600px; margin-bottom: 12px;">
                        <div style="width: 120px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right; padding-right: 20px;">Screened</div>
                        <div style="flex-grow: 1; height: 12px; display: flex; padding: 0 5%;">
                            <div style="width: 100%; background: #cce8d6; border-radius: 6px; display: flex; overflow: hidden;">
                                <div style="width: <?php echo $screened_pct; ?>%; background: #16a34a;"></div>
                            </div>
                        </div>
                        <div style="width: 60px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right;"><?php echo $screened_count; ?></div>
                    </div>
                    <!-- Connector line -->
                    <div style="height: 20px; width: 1px; background: #e2e8f0; margin-bottom: 12px;"></div>
                    
                    <!-- Interviewed -->
                    <div style="display: flex; align-items: center; width: 100%; max-width: 600px; margin-bottom: 12px;">
                        <div style="width: 120px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right; padding-right: 20px;">Interviewed</div>
                        <div style="flex-grow: 1; height: 12px; display: flex; padding: 0 10%;">
                            <div style="width: 100%; background: #cce8d6; border-radius: 6px; display: flex; overflow: hidden;">
                                <div style="width: <?php echo $interviewed_pct; ?>%; background: #16a34a;"></div>
                            </div>
                        </div>
                        <div style="width: 60px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right;"><?php echo $interviewed_count; ?></div>
                    </div>
                    <!-- Connector line -->
                    <div style="height: 20px; width: 1px; background: #e2e8f0; margin-bottom: 12px;"></div>
                    
                    <!-- Offered -->
                    <div style="display: flex; align-items: center; width: 100%; max-width: 600px; margin-bottom: 12px;">
                        <div style="width: 120px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right; padding-right: 20px;">Offered</div>
                        <div style="flex-grow: 1; height: 12px; display: flex; padding: 0 15%;">
                            <div style="width: 100%; background: #cce8d6; border-radius: 6px; display: flex; overflow: hidden;">
                                <div style="width: <?php echo $offered_pct; ?>%; background: #16a34a;"></div>
                            </div>
                        </div>
                        <div style="width: 60px; font-weight: 600; font-size: 14px; color: #1e293b; text-align: right;"><?php echo $offered_count; ?></div>
                    </div>
                    <!-- Connector line -->
                    <div style="height: 20px; width: 1px; background: #e2e8f0; margin-bottom: 12px;"></div>
                    
                    <!-- Hired -->
                    <div style="display: flex; align-items: center; width: 100%; max-width: 600px;">
                        <div style="width: 120px;"></div>
                        <div style="flex-grow: 1; background: #16a34a; border-radius: 8px; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; color: white;">
                            <span style="font-weight: 600; font-size: 15px;">Hired</span>
                            <div style="height: 6px; width: 40px; background: rgba(255,255,255,0.3); border-radius: 3px;"></div>
                            <span style="font-weight: 700; font-size: 16px;"><?php echo $hired_count; ?></span>
                        </div>
                        <div style="width: 60px;"></div>
                    </div>
                </div>
            </div>

            <!-- Active Job Openings Table -->
            <div class="xhr-card">
                <div style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #0f172a;">Active Job Openings</h3>
                    <div style="display: flex; gap: 8px; color: #64748b;">
                        <button style="background: none; border: none; cursor: pointer; color: inherit;"><span class="material-symbols-outlined">filter_list</span></button>
                        <button style="background: none; border: none; cursor: pointer; color: inherit;"><span class="material-symbols-outlined">more_vert</span></button>
                    </div>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 12px 1.5rem; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">JOB ROLE</th>
                            <th style="padding: 12px 1.5rem; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">DEPT</th>
                            <th style="padding: 12px 1.5rem; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">APPLICANTS</th>
                            <th style="padding: 12px 1.5rem; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">STATUS</th>
                            <th style="padding: 12px 1.5rem; text-align: right; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['active_openings_list'])): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 24px; color: #94a3b8; font-size: 14px;">No active job openings.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($stats['active_openings_list'] as $jo): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 1rem 1.5rem;">
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">
                                    <a href="<?php echo $base . '/recruitment/job_openings/edit/' . $jo->id; ?>" style="color:#1e293b; text-decoration:none;">
                                        <?php echo htmlspecialchars($jo->title); ?>
                                    </a>
                                </div>
                                <div style="font-size: 12px; color: #64748b;">Posted <?php echo function_exists('time_ago') ? time_ago($jo->date_created) : _d($jo->date_created); ?></div>
                            </td>
                            <td style="padding: 1rem 1.5rem; color: #475569; font-size: 13px;"><?php echo htmlspecialchars($jo->department_name ?? '—'); ?></td>
                            <td style="padding: 1rem 1.5rem; font-weight: 600; color: #0f172a;"><?php echo $jo->applicant_count; ?></td>
                            <td style="padding: 1rem 1.5rem;">
                                <span style="background: #dcfce7; color: #16a34a; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; text-transform: uppercase;">ACTIVE</span>
                            </td>
                            <td style="padding: 1rem 1.5rem; text-align: right;">
                                <a href="<?php echo $base . '/recruitment/applicants?opening_id=' . $jo->id; ?>" style="color: #16a34a; font-weight: 600; font-size: 13px;">View Pipeline</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Pipeline Velocity & Upcoming Interviews -->
            <div class="xhr-card">
                <div style="padding: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem 0; font-size: 16px; font-weight: 600; color: #0f172a;">Pipeline Velocity</h3>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: #1e293b;">Screening to Interview</span>
                            <span style="font-size: 13px; font-weight: 700; color: #16a34a;"><?php echo $stats['screening_to_interview_rate'] ?? 0; ?>%</span>
                        </div>
                        <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                            <div style="width: <?php echo $stats['screening_to_interview_rate'] ?? 0; ?>%; height: 100%; background: #16a34a;"></div>
                        </div>
                        <div style="font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-outlined" style="font-size: 14px; color: #16a34a;">trending_up</span>
                            Conversion funnel status
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: #1e293b;">Interview to Offer</span>
                            <span style="font-size: 13px; font-weight: 700; color: #2563eb;"><?php echo $stats['interview_to_offer_rate'] ?? 0; ?>%</span>
                        </div>
                        <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                            <div style="width: <?php echo $stats['interview_to_offer_rate'] ?? 0; ?>%; height: 100%; background: #2563eb;"></div>
                        </div>
                        <div style="font-size: 11px; color: #64748b;">
                            Hiring selection status
                        </div>
                    </div>
                </div>
                
                <div style="height: 1px; background: #e2e8f0;"></div>
                
                <div style="padding: 1.5rem;">
                    <p style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; margin: 0 0 1rem 0;">Upcoming Interviews</p>
                    
                    <?php if (empty($stats['upcoming_interviews'])): ?>
                    <p style="font-size: 13px; color: #64748b; margin: 0 0 1rem 0;">No upcoming interviews scheduled.</p>
                    <?php else: ?>
                    <?php foreach ($stats['upcoming_interviews'] as $ui):
                        $initials = '';
                        $names = explode(' ', $ui->applicant_name);
                        foreach ($names as $n) { if (!empty($n)) $initials .= strtoupper($n[0]); }
                        $initials = substr($initials, 0, 2);
                    ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: #e0f2fe; color: #0284c7; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; border-radius: 50%;"><?php echo $initials; ?></div>
                            <div>
                                <div style="font-size: 14px; font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($ui->applicant_name); ?></div>
                                <div style="font-size: 12px; color: #64748b;"><?php echo htmlspecialchars($ui->opening_title ?? 'General Interview'); ?> • <?php echo _d($ui->interview_date); ?><?php echo $ui->from_time ? ' ' . date('H:i', strtotime($ui->from_time)) : ''; ?></div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined" style="color: #64748b; font-size: 20px;">videocam</span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <a href="<?php echo $base . '/recruitment/interviews'; ?>" class="btn btn-default" style="width: 100%; display: inline-flex; justify-content: center; font-weight: 600; border-radius: 6px;">View Full Schedule</a>
                </div>
            </div>

            <!-- Recruitment Setup -->
            <div class="xhr-card" style="background: #1e293b; color: white;">
                <div style="padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 16px; font-weight: 600; color: #10b981;">Recruitment Setup</h3>
                            <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.4;">Manage your staffing plans and interview workflows in one place.</p>
                        </div>
                        <div style="background: #10b981; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);">
                            <span class="material-symbols-outlined" style="color: white; font-size: 20px;">chat_bubble</span>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1.5rem;">
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="color: #10b981;">post_add</span>
                            <span style="font-size: 12px; font-weight: 500;">New Plan</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="color: #10b981;">description</span>
                            <span style="font-size: 12px; font-weight: 500;">Template</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="color: #10b981;">sync</span>
                            <span style="font-size: 12px; font-weight: 500;">Workflow</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="color: #10b981;">linear_scale</span>
                            <span style="font-size: 12px; font-weight: 500;">Rounds</span>
                        </div>
                    </div>
                    
                    <button class="xhr-btn xhr-btn--primary" style="width: 100%; justify-content: center; background: #10b981; border-color: #10b981; color: white;">
                        <span class="material-symbols-outlined">bolt</span>
                        Quick Requisition
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
