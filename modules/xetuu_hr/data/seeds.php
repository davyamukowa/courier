<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Xetuu HR — Default seed data
 * Runs once on first install (tables must be empty).
 * company_id = 0 means "global default" — applies to any company.
 */

$CI  = &get_instance();
$p   = db_prefix();
$now = date('Y-m-d H:i:s');

// ── DEPARTMENTS ───────────────────────────────────────────────────────────────
if ($CI->db->count_all($p . 'hr_departments') == 0) {

    // Insert "All Departments" root group, capture its ID
    $CI->db->insert($p . 'hr_departments', [
        'company_id'   => 0,
        'name'         => 'All Departments',
        'parent_id'    => null,
        'is_group'     => 1,
        'active'       => 1,
        'date_created' => $now,
    ]);
    $root_id = $CI->db->insert_id();

    // All standard departments — parent = root "All Departments"
    // Organised by industry/function; each installs as a flat leaf under the root.
    $departments = [

        // ── Core / Universal ────────────────────────────────────────────────
        'Management',
        'Human Resources',
        'Finance',
        'Accounts',
        'Payroll',
        'Internal Audit',
        'Information Technology',
        'Administration',
        'Legal',
        'Compliance',
        'Risk Management',
        'Quality Management',
        'Research & Development',
        'Strategy & Planning',
        'Business Development',
        'Customer Service',
        'Operations',

        // ── Sales, Marketing & Commerce ──────────────────────────────────────
        'Sales',
        'Marketing',
        'Digital Marketing',
        'Brand Management',
        'Public Relations',
        'Events & Sponsorships',
        'Retail Sales',
        'Trade & Export',
        'Channel & Partner Management',
        'Pre-Sales & Solutions',

        // ── Supply Chain, Logistics & Procurement ────────────────────────────
        'Purchase',
        'Procurement',
        'Supply Chain',
        'Warehouse & Stores',
        'Inventory Management',
        'Logistics',
        'Dispatch',
        'Transport & Fleet',
        'Customs & Clearing',

        // ── Production & Engineering ─────────────────────────────────────────
        'Production',
        'Manufacturing',
        'Engineering',
        'Mechanical Engineering',
        'Electrical Engineering',
        'Civil Engineering',
        'Process Engineering',
        'Maintenance & Reliability',
        'Quality Control',
        'Health, Safety & Environment (HSE)',
        'Facilities & Maintenance',

        // ── Project & Programme Management ───────────────────────────────────
        'Project Management Office (PMO)',
        'Programme Management',
        'Change Management',

        // ── Technology & Data ─────────────────────────────────────────────────
        'Software Development',
        'Infrastructure & Networks',
        'Cybersecurity',
        'Data & Business Intelligence',
        'IT Support & Helpdesk',
        'Cloud & DevOps',
        'Digital Transformation',
        'ERP & Systems Administration',

        // ── Financial Services & Banking ──────────────────────────────────────
        'Treasury',
        'Credit & Loans',
        'Investments',
        'Retail Banking',
        'Corporate Banking',
        'Insurance Underwriting',
        'Claims Management',
        'Actuarial Services',
        'Anti-Money Laundering (AML)',
        'Trade Finance',

        // ── Healthcare & Medical ──────────────────────────────────────────────
        'Clinical Services',
        'Nursing & Midwifery',
        'Pharmacy',
        'Medical Laboratory',
        'Radiology & Imaging',
        'Surgery & Theatre',
        'Emergency & Casualty',
        'Outpatient Services',
        'Physiotherapy & Rehabilitation',
        'Mental Health & Counselling',
        'Nutrition & Dietetics',
        'Dental Services',
        'Medical Records',
        'Infection Prevention & Control',
        'Community Health',
        'Biomedical Engineering',

        // ── Education & Academic ──────────────────────────────────────────────
        'Academic Affairs',
        'Student Affairs',
        'Curriculum & Quality',
        'Examinations & Assessment',
        'Research & Publications',
        'Library & Information Services',
        'Alumni Relations',
        'Technical & Vocational Training',
        'Early Childhood Development (ECD)',

        // ── Hospitality & Tourism ─────────────────────────────────────────────
        'Front Office & Reservations',
        'Housekeeping & Laundry',
        'Food & Beverage',
        'Kitchen & Culinary',
        'Spa & Recreation',
        'Revenue Management',
        'Events & Banqueting',
        'Concierge & Guest Experience',
        'Tours & Travel',

        // ── Construction & Real Estate ────────────────────────────────────────
        'Site Management',
        'Architecture & Planning',
        'Quantity Surveying',
        'Mechanical, Electrical & Plumbing (MEP)',
        'Contract Management',
        'Property Sales & Leasing',
        'Property Management',

        // ── Agriculture & Agribusiness ────────────────────────────────────────
        'Crop Production',
        'Livestock & Animal Husbandry',
        'Irrigation & Water Management',
        'Agricultural Engineering',
        'Veterinary Services',
        'Post-Harvest & Grading',
        'Farm Operations',

        // ── Manufacturing & Production ────────────────────────────────────────
        'Packaging',
        'Production Planning & Scheduling',
        'Tooling & Equipment',
        'FMCG Production',
        'Food Processing',
        'Textile & Garments',
        'Printing & Publishing',

        // ── Media, Arts & Entertainment ───────────────────────────────────────
        'Editorial',
        'Journalism & Reporting',
        'Broadcast & Production',
        'Photography & Videography',
        'Animation & Graphics',
        'Sound & Audio',
        'Content Creation',
        'Film & TV Production',

        // ── NGO, Social Services & Government ────────────────────────────────
        'Programme Management (NGO)',
        'Monitoring & Evaluation (M&E)',
        'Community Development',
        'Social Work & Welfare',
        'Grants & Fundraising',
        'Government Relations',
        'Donor & Partnership Relations',
        'Public Administration',

        // ── Security & Loss Prevention ────────────────────────────────────────
        'Physical Security',
        'Loss Prevention',
        'Investigation & Intelligence',
        'Access Control & CCTV',
        'VIP Protection',

        // ── Retail & Commerce ─────────────────────────────────────────────────
        'Store Operations',
        'Merchandising',
        'Visual Merchandising',
        'E-Commerce',

        // ── Telecommunications ────────────────────────────────────────────────
        'Network Operations',
        'Radio Frequency (RF) Engineering',
        'Switching & Transmission',
        'Customer Experience (Telco)',
        'Regulatory Affairs (Telco)',

        // ── Corporate Affairs & Governance ───────────────────────────────────
        'Corporate Secretary',
        'Investor Relations',
        'Mergers & Acquisitions',
        'Sustainability & ESG',
        'Corporate Social Responsibility (CSR)',
        'Intellectual Property',
        'Data Privacy & Protection',
    ];

    foreach ($departments as $name) {
        $CI->db->insert($p . 'hr_departments', [
            'company_id'   => 0,
            'name'         => $name,
            'parent_id'    => $root_id,
            'is_group'     => 0,
            'active'       => 1,
            'date_created' => $now,
        ]);
    }
}

// ── DESIGNATIONS ──────────────────────────────────────────────────────────────
if ($CI->db->count_all($p . 'hr_designations') == 0) {

    $designations = [

        // ── Board & C-Suite ───────────────────────────────────────────────────
        ['Chairman / Chairperson',                    'Presides over the Board of Directors'],
        ['Chief Executive Officer (CEO)',             'Highest-ranking executive; responsible for overall strategy and operations'],
        ['Managing Director (MD)',                    'Responsible for day-to-day management of the company (common in Commonwealth jurisdictions)'],
        ['Executive Director',                        'Senior board-level executive with direct operational responsibilities'],
        ['Non-Executive Director',                    'Board member not involved in daily management; provides independent oversight'],
        ['Independent Director',                      'Non-executive board member with no material relationship with the company'],
        ['Chief Operating Officer (COO)',             'Oversees day-to-day operational functions'],
        ['Chief Financial Officer (CFO)',             'Responsible for financial planning, reporting and risk'],
        ['Chief Technology Officer (CTO)',            'Leads technology strategy and product development'],
        ['Chief Information Officer (CIO)',           'Manages IT strategy and information systems'],
        ['Chief Human Resources Officer (CHRO)',      'Leads human capital strategy and HR functions'],
        ['Chief People Officer (CPO)',                'Champion of people culture and employee experience'],
        ['Chief Marketing Officer (CMO)',             'Oversees brand, marketing strategy and communications'],
        ['Chief Revenue Officer (CRO)',               'Drives revenue strategy across sales, marketing and partnerships'],
        ['Chief Legal Officer (CLO)',                 'Provides legal counsel; also known as General Counsel'],
        ['Chief Risk Officer (CRO)',                  'Manages enterprise-wide risk management framework'],
        ['Chief Compliance Officer (CCO)',            'Ensures regulatory and policy compliance across the organisation'],
        ['Chief Data Officer (CDO)',                  'Responsible for data governance, analytics strategy and data assets'],
        ['Chief Strategy Officer (CSO)',              'Leads corporate strategy and long-range planning'],
        ['Chief Commercial Officer (CCO)',            'Oversees commercial activities including sales and partnerships'],
        ['Chief Customer Officer (CCO)',              'Champions customer experience and success organisation-wide'],
        ['Chief Security Officer (CSO)',              'Oversees both physical and cybersecurity programmes'],
        ['Chief Product Officer (CPO)',               'Leads product vision, roadmap and development'],
        ['Chief Procurement Officer (CPO)',           'Manages sourcing strategy and supplier relationships'],

        // ── President / SVP / VP ──────────────────────────────────────────────
        ['President',                                 'Senior executive; in some structures, equivalent to or above CEO'],
        ['Executive Vice President (EVP)',            'Senior vice-president reporting directly to CEO or President'],
        ['Senior Vice President (SVP)',               'Senior leadership tier, above VP'],
        ['Vice President (VP)',                       'Senior management with functional or regional accountability'],
        ['Associate Vice President (AVP)',            'Junior VP-tier role, common in financial services'],

        // ── Director Level ────────────────────────────────────────────────────
        ['Director',                                  'Senior functional leader responsible for a department or business unit'],
        ['Senior Director',                           'Experienced director overseeing multiple teams or sub-functions'],
        ['Associate Director',                        'Mid-to-senior director, typically one step below full Director'],
        ['Deputy Director',                           'Second-in-command to a Director'],
        ['Assistant Director',                        'Supports the Director in a defined area of responsibility'],
        ['Regional Director',                         'Director accountable for a geographic region'],
        ['Country Director',                          'Head of all operations in a specific country'],
        ['Divisional Director',                       'Director responsible for a major business division'],
        ['Group Director',                            'Oversees multiple business units or subsidiaries at group level'],

        // ── General Management ────────────────────────────────────────────────
        ['General Manager',                           'Overall responsibility for a business unit, site or branch'],
        ['Deputy General Manager',                    'Second-in-command to the General Manager'],
        ['Regional Manager',                          'Manages operations across a defined region'],
        ['Country Manager',                           'Manages all activities within a specific country'],
        ['Area Manager',                              'Manages multiple locations or teams within a geographic area'],
        ['Branch Manager',                            'Manages a single branch or outlet'],
        ['Plant Manager / Factory Manager',           'Overall responsibility for a manufacturing plant or factory'],
        ['Site Manager',                              'Manages a specific project site or operational site'],

        // ── Departmental Management ───────────────────────────────────────────
        ['Head of Department',                        'Senior leader responsible for a specific department or function'],
        ['Department Manager',                        'Manages a department and its staff'],
        ['Senior Manager',                            'Experienced manager overseeing complex functions or large teams'],
        ['Manager',                                   'Manages a team, project or business function'],
        ['Deputy Manager',                            'Second-in-command to a Manager'],
        ['Assistant Manager',                         'Supports the Manager in their day-to-day responsibilities'],
        ['Principal Officer',                         'Senior professional officer with significant decision-making authority'],

        // ── Supervisory & Coordination ────────────────────────────────────────
        ['Team Lead / Team Leader',                   'Leads a small team, often a senior individual contributor with line management'],
        ['Supervisor',                                'Directly oversees front-line staff or a work unit'],
        ['Senior Supervisor',                         'Experienced supervisor with broader span of control'],
        ['Coordinator',                               'Organises activities, projects or resources across a function'],
        ['Senior Coordinator',                        'Experienced coordinator with additional responsibility'],
        ['Senior Officer',                            'Senior individual contributor or team leader'],

        // ── Finance & Accounting ──────────────────────────────────────────────
        ['Finance Director',                          'Senior finance leader; may also hold CFO responsibilities in smaller organisations'],
        ['Finance Manager',                           'Manages financial operations, reporting and analysis'],
        ['Financial Controller',                      'Oversees accounting, compliance and financial reporting'],
        ['Chief Accountant',                          'Head of accounting function'],
        ['Management Accountant',                     'Provides internal financial information for decision-making'],
        ['Senior Accountant',                         'Experienced accountant handling complex transactions'],
        ['Accountant',                                'Manages accounts, ledgers and financial records'],
        ['Junior Accountant',                         'Entry-level accounting role supporting senior accountants'],
        ['Accounts Clerk',                            'Processes and records financial transactions'],
        ['Cost Accountant',                           'Analyses production and operational costs'],
        ['Senior Financial Analyst',                  'Experienced analyst providing financial modelling and insights'],
        ['Financial Analyst',                         'Analyses financial data, budgets and forecasts'],
        ['Budget Analyst',                            'Monitors and controls organisational budgets'],
        ['Treasury Manager',                          'Manages cash, liquidity and financial risk'],
        ['Treasury Analyst',                          'Supports treasury operations and cash management'],
        ['Tax Manager',                               'Oversees tax planning, compliance and reporting'],
        ['Senior Tax Analyst',                        'Experienced analyst handling complex tax matters'],
        ['Tax Officer',                               'Manages day-to-day tax compliance and filings'],
        ['Payroll Manager',                           'Oversees the payroll function and statutory compliance'],
        ['Payroll Officer',                           'Processes payroll and maintains payroll records'],
        ['Payroll Clerk',                             'Supports payroll processing and administration'],
        ['Audit Manager',                             'Leads internal audit planning and execution'],
        ['Senior Internal Auditor',                   'Experienced auditor conducting complex audit assignments'],
        ['Internal Auditor',                          'Evaluates internal controls and operational processes'],
        ['Credit Analyst',                            'Assesses creditworthiness of clients or counterparties'],
        ['Investment Analyst',                        'Analyses investment opportunities and portfolio performance'],
        ['Accounts Payable Officer',                  'Manages supplier invoices and payments'],
        ['Accounts Receivable Officer',               'Manages client invoicing and collections'],
        ['Billing Officer',                           'Prepares and issues client invoices'],

        // ── Human Resources ───────────────────────────────────────────────────
        ['HR Director',                               'Leads the HR function and people strategy'],
        ['HR Manager',                                'Manages HR operations and staff relations'],
        ['HR Business Partner (HRBP)',                'Provides strategic HR support aligned to business units'],
        ['HR Generalist',                             'Handles broad range of HR functions across the employee lifecycle'],
        ['HR Specialist',                             'Specialist in a specific HR domain (e.g. benefits, ER)'],
        ['HR Officer',                                'Delivers day-to-day HR services and administration'],
        ['HR Assistant',                              'Provides administrative support to the HR team'],
        ['HR Clerk',                                  'Handles HR filing, records and administrative tasks'],
        ['HR Analyst',                                'Analyses HR data, trends and metrics'],
        ['HR Operations Manager',                     'Manages HR systems, processes and administration'],
        ['People Operations Manager',                 'Oversees employee experience, HR tech and HR operations'],
        ['Talent Acquisition Manager',                'Leads recruitment strategy and talent pipeline'],
        ['Senior Recruiter',                          'Manages end-to-end recruitment for senior or complex roles'],
        ['Recruiter',                                 'Sources, screens and places candidates'],
        ['Recruitment Coordinator',                   'Coordinates recruitment logistics and candidate experience'],
        ['Learning & Development Manager',            'Designs and delivers employee learning programmes'],
        ['Training Specialist',                       'Develops and facilitates training content and workshops'],
        ['Training Officer',                          'Coordinates and delivers training activities'],
        ['Compensation & Benefits Manager',           'Designs and manages remuneration and benefits programmes'],
        ['Compensation & Benefits Specialist',        'Administers pay structures, benefits and benchmarking'],
        ['Employee Relations Manager',                'Manages workplace relations, grievances and disciplinary matters'],
        ['Employee Relations Officer',                'Handles day-to-day ER matters and staff queries'],
        ['Organisational Development Specialist',     'Drives OD interventions, culture and effectiveness programmes'],
        ['Workforce Planning Analyst',                'Analyses staffing needs and headcount planning'],
        ['Industrial Relations Officer',              'Manages relations with trade unions and collective bargaining'],

        // ── Sales & Business Development ──────────────────────────────────────
        ['Sales Director',                            'Leads national or divisional sales strategy and teams'],
        ['National Sales Manager',                    'Manages countrywide sales operations'],
        ['Regional Sales Manager',                    'Manages sales across a defined region'],
        ['Area Sales Manager',                        'Manages sales within a specific geographic territory'],
        ['Sales Manager',                             'Manages a sales team and pipeline'],
        ['Key Account Director',                      'Owns relationships with the most strategic clients'],
        ['Senior Key Account Manager',                'Manages large, complex client accounts'],
        ['Key Account Manager',                       'Manages relationships with key clients'],
        ['Senior Account Manager',                    'Manages a portfolio of accounts with high complexity'],
        ['Account Manager',                           'Manages client relationships and account growth'],
        ['Account Executive',                         'Manages client accounts and supports sales growth'],
        ['Senior Sales Executive',                    'Experienced sales professional handling major deals'],
        ['Sales Executive',                           'Manages sales activities and client acquisition'],
        ['Sales Representative',                      'Front-line sales role; prospecting and selling'],
        ['Business Development Director',             'Leads market expansion and strategic growth initiatives'],
        ['Business Development Manager',              'Identifies and develops new business opportunities'],
        ['Business Development Executive',            'Supports BD activities including lead generation'],
        ['Business Development Associate',            'Entry-level BD role supporting the BD pipeline'],
        ['Pre-Sales Engineer',                        'Provides technical expertise during the sales process'],
        ['Sales Operations Manager',                  'Manages CRM, sales data and sales process efficiency'],
        ['Sales Analyst',                             'Analyses sales data, performance and trends'],
        ['Channel Manager',                           'Manages partner and reseller channels'],
        ['Inside Sales Representative',               'Conducts sales remotely via phone, email or digital channels'],
        ['Field Sales Representative',                'Conducts in-person sales in assigned territory'],
        ['Telesales Agent',                           'Sells products or services via telephone outreach'],
        ['Retail Sales Associate',                    'Front-line retail selling and customer assistance'],

        // ── Marketing & Communications ────────────────────────────────────────
        ['Marketing Director',                        'Leads brand, marketing strategy and communications'],
        ['Marketing Manager',                         'Manages marketing campaigns, teams and budgets'],
        ['Brand Manager',                             'Manages brand positioning, identity and consistency'],
        ['Digital Marketing Manager',                 'Leads digital channels including SEO, SEM, social and email'],
        ['Digital Marketing Specialist',              'Executes digital campaigns across online channels'],
        ['SEO Specialist',                            'Optimises website content and technical SEO for search rankings'],
        ['SEM / PPC Specialist',                      'Manages paid search and pay-per-click campaigns'],
        ['Social Media Manager',                      'Manages social media presence, strategy and community'],
        ['Social Media Specialist',                   'Creates and publishes social media content'],
        ['Content Manager',                           'Oversees content strategy, production and distribution'],
        ['Senior Content Writer',                     'Produces high-quality written content for brand and campaigns'],
        ['Content Writer / Copywriter',               'Creates written content for marketing, web and communications'],
        ['Creative Director',                         'Leads creative vision for brand, campaigns and design'],
        ['Art Director',                              'Oversees visual direction of design and creative assets'],
        ['Senior Graphic Designer',                   'Produces complex design assets and guides junior designers'],
        ['Graphic Designer',                          'Creates visual content for digital and print'],
        ['UX Designer',                               'Designs user experiences and interaction flows'],
        ['UI Designer',                               'Designs visual user interfaces for digital products'],
        ['UX/UI Designer',                            'Handles both user experience and user interface design'],
        ['Product Designer',                          'Designs products and digital experiences end-to-end'],
        ['Marketing Analyst',                         'Analyses marketing performance, campaigns and consumer data'],
        ['Market Research Analyst',                   'Conducts consumer and market research studies'],
        ['PR & Communications Manager',               'Manages public relations, media relations and corporate communications'],
        ['PR Officer',                                'Handles day-to-day PR activities and media queries'],
        ['Communications Manager',                    'Manages internal and external communications'],
        ['Events Manager',                            'Plans and executes events, activations and sponsorships'],
        ['Events Coordinator',                        'Coordinates logistics for events and activations'],

        // ── Information Technology ────────────────────────────────────────────
        ['IT Director / Head of IT',                  'Leads IT strategy, infrastructure and digital services'],
        ['IT Manager',                                'Manages IT operations, systems and support teams'],
        ['Enterprise Architect',                      'Designs and governs the overall IT and business architecture'],
        ['Solutions Architect',                       'Designs end-to-end technical solutions for business problems'],
        ['Technical Architect',                       'Provides technical design authority for complex systems'],
        ['Cloud Architect',                           'Designs cloud infrastructure and migration strategies'],
        ['Software Architect',                        'Defines software architecture patterns and standards'],
        ['Data Architect',                            'Designs data models, pipelines and governance frameworks'],
        ['Principal Software Engineer',               'Most senior individual contributor in software engineering'],
        ['Lead Software Engineer / Tech Lead',        'Technical lead of a software engineering team'],
        ['Senior Software Engineer',                  'Experienced engineer delivering complex software solutions'],
        ['Software Engineer',                         'Develops, tests and maintains software systems'],
        ['Junior Software Engineer',                  'Entry-level software development role'],
        ['Full Stack Developer',                      'Develops both frontend and backend components of applications'],
        ['Frontend Developer',                        'Specialises in user interface and client-side development'],
        ['Backend Developer',                         'Specialises in server-side logic, APIs and databases'],
        ['Mobile Developer',                          'Builds mobile applications for iOS and/or Android'],
        ['DevOps Engineer',                           'Bridges development and operations; manages CI/CD and infrastructure'],
        ['Site Reliability Engineer (SRE)',           'Ensures reliability, scalability and performance of production systems'],
        ['Cloud Engineer',                            'Designs, deploys and manages cloud infrastructure'],
        ['Data Engineer',                             'Builds data pipelines, warehouses and ETL processes'],
        ['Senior Data Scientist',                     'Leads data science modelling, experimentation and insight generation'],
        ['Data Scientist',                            'Develops models and derives insights from complex data'],
        ['Machine Learning Engineer',                 'Builds and deploys machine learning models in production'],
        ['AI Engineer',                               'Develops and implements artificial intelligence solutions'],
        ['BI Developer',                              'Builds business intelligence dashboards and reports'],
        ['BI Analyst',                                'Analyses business data and produces insights via BI tools'],
        ['Database Administrator (DBA)',              'Manages, optimises and secures database systems'],
        ['Systems Administrator',                     'Manages servers, operating systems and system infrastructure'],
        ['Network Engineer',                          'Designs and manages LAN, WAN and network infrastructure'],
        ['Network Administrator',                     'Administers and maintains network equipment and connectivity'],
        ['Cybersecurity Manager / CISO',              'Leads the information security programme and risk management'],
        ['Cybersecurity Engineer',                    'Designs and implements security controls and systems'],
        ['Cybersecurity Analyst',                     'Monitors, detects and responds to security threats'],
        ['Penetration Tester',                        'Conducts authorised security testing to identify vulnerabilities'],
        ['IT Support Manager',                        'Manages the IT support and helpdesk function'],
        ['IT Support Engineer',                       'Provides second/third-line technical support'],
        ['IT Support Specialist',                     'Provides day-to-day end-user technical support'],
        ['Helpdesk Technician',                       'First-line technical support for end users'],
        ['QA Engineer / Test Engineer',               'Designs and executes software testing strategies'],
        ['Test Automation Engineer',                  'Builds and maintains automated testing frameworks'],
        ['Product Manager',                           'Defines product vision, roadmap and prioritisation'],
        ['Product Owner',                             'Manages the product backlog and sprint goals in an Agile team'],
        ['Scrum Master',                              'Facilitates Agile ceremonies and removes team impediments'],
        ['ERP Administrator',                         'Manages, configures and supports ERP systems'],

        // ── Engineering & Technical ───────────────────────────────────────────
        ['Chief Engineer',                            'Most senior engineering authority in an organisation'],
        ['Principal Engineer',                        'Senior engineering specialist; technical authority in a discipline'],
        ['Lead Engineer',                             'Leads an engineering team or complex project workstream'],
        ['Senior Engineer',                           'Experienced engineer handling complex technical work'],
        ['Engineer',                                  'Professional engineer in a specified discipline'],
        ['Graduate Engineer',                         'Entry-level engineer on a graduate development programme'],
        ['Junior Engineer',                           'Early-career engineer supporting engineering activities'],
        ['Technical Lead',                            'Technical authority within an engineering team'],
        ['Mechanical Engineer',                       'Designs and maintains mechanical systems and equipment'],
        ['Electrical Engineer',                       'Designs and manages electrical power and control systems'],
        ['Civil Engineer',                            'Designs infrastructure, buildings and civil works'],
        ['Structural Engineer',                       'Designs structural systems for buildings and infrastructure'],
        ['Chemical Engineer',                         'Designs chemical processes and production plants'],
        ['Environmental Engineer',                    'Manages environmental compliance and impact mitigation'],
        ['Industrial Engineer',                       'Optimises production systems and manufacturing processes'],
        ['Process Engineer',                          'Designs and improves manufacturing or chemical processes'],
        ['Maintenance Engineer',                      'Manages preventive and corrective maintenance of equipment'],
        ['Reliability Engineer',                      'Ensures equipment reliability and minimises downtime'],
        ['Instrumentation & Control Engineer',        'Designs and maintains instrumentation and automation systems'],
        ['Telecommunications Engineer',               'Designs and manages telecoms networks and systems'],
        ['Biomedical Engineer',                       'Maintains and manages medical equipment and technology'],
        ['Field Engineer',                            'Provides technical support and installations at client or field sites'],
        ['Senior Technician',                         'Experienced technician handling complex technical tasks'],
        ['Technician',                                'Performs technical installation, maintenance or repair work'],
        ['Junior Technician',                         'Entry-level technical support role'],
        ['Maintenance Technician',                    'Performs routine and corrective maintenance on equipment'],
        ['Electrical Technician',                     'Installs, maintains and repairs electrical systems'],
        ['Mechanical Technician',                     'Maintains and repairs mechanical systems and machinery'],
        ['Draughtsman / CAD Technician',              'Produces technical drawings and CAD models'],

        // ── Legal, Risk & Compliance ──────────────────────────────────────────
        ['Legal Director / Head of Legal',            'Leads the legal function and provides strategic legal guidance'],
        ['Senior Legal Counsel',                      'Provides senior legal advice on complex matters'],
        ['Legal Counsel / In-House Counsel',          'Provides day-to-day legal advice and manages legal risk'],
        ['Associate Legal Counsel',                   'Junior counsel providing legal research and support'],
        ['Legal Officer',                             'Handles routine legal administration and compliance tasks'],
        ['Legal Assistant',                           'Provides administrative support to the legal team'],
        ['Paralegal',                                 'Assists lawyers with research, documentation and case management'],
        ['Company Secretary',                         'Ensures corporate governance compliance and board secretariat function'],
        ['Assistant Company Secretary',               'Supports the Company Secretary in governance activities'],
        ['Contracts Manager',                         'Manages the full lifecycle of commercial contracts'],
        ['Contracts Specialist',                      'Reviews, drafts and negotiates contracts'],
        ['Compliance Manager',                        'Manages the compliance programme and regulatory adherence'],
        ['Compliance Officer',                        'Monitors and enforces compliance with laws, regulations and policies'],
        ['Compliance Analyst',                        'Analyses compliance data and supports the compliance function'],
        ['Risk Manager',                              'Identifies, assesses and mitigates enterprise risks'],
        ['Risk Analyst',                              'Conducts risk assessments and monitors risk registers'],
        ['Data Protection Officer (DPO)',             'Responsible for data privacy compliance (e.g. GDPR, local data laws)'],
        ['Regulatory Affairs Manager',                'Manages regulatory submissions and relationships with regulators'],
        ['Regulatory Affairs Officer',                'Handles day-to-day regulatory compliance activities'],
        ['AML Officer',                               'Monitors for money laundering, fraud and sanctions breaches'],
        ['Fraud Investigation Officer',               'Investigates fraud incidents and financial crime'],
        ['Insurance Manager',                         'Manages corporate insurance portfolio and claims'],
        ['Claims Officer',                            'Processes and resolves insurance or customer claims'],

        // ── Customer Service & Support ────────────────────────────────────────
        ['Customer Service Director',                 'Leads customer service and experience strategy'],
        ['Customer Service Manager',                  'Manages the customer service team and service standards'],
        ['Customer Service Supervisor',               'Supervises customer service agents and daily operations'],
        ['Customer Service Representative',           'Front-line customer support via phone, email or chat'],
        ['Customer Care Agent',                       'Handles inbound customer enquiries and complaints'],
        ['Customer Success Director',                 'Leads customer success and retention strategy'],
        ['Customer Success Manager',                  'Manages customer relationships to ensure value realisation'],
        ['Customer Success Specialist',               'Supports customers in onboarding and maximising product value'],
        ['Technical Support Manager',                 'Manages the technical support team and escalation processes'],
        ['Senior Technical Support Engineer',         'Handles complex technical escalations and customer issues'],
        ['Technical Support Engineer',                'Provides technical assistance to customers'],
        ['Help Desk Analyst',                         'First-line support for customer and internal user queries'],
        ['Contact Centre Manager',                    'Manages contact centre operations, SLAs and teams'],
        ['Contact Centre Supervisor',                 'Supervises contact centre agents and call quality'],
        ['Contact Centre Agent',                      'Handles customer interactions via phone, email and chat'],
        ['Client Relations Manager',                  'Manages strategic client relationships and satisfaction'],
        ['Client Services Officer',                   'Provides day-to-day support and service to clients'],

        // ── Operations & Facilities ───────────────────────────────────────────
        ['Operations Director',                       'Leads overall operational strategy and execution'],
        ['Operations Manager',                        'Manages operational processes, teams and performance'],
        ['Operations Supervisor',                     'Supervises operational staff and workflow'],
        ['Operations Analyst',                        'Analyses operational data and identifies improvement opportunities'],
        ['Operations Officer',                        'Handles day-to-day operational activities'],
        ['Process Improvement Manager',               'Leads Lean, Six Sigma or continuous improvement programmes'],
        ['Facilities Manager',                        'Manages buildings, office services and property operations'],
        ['Facilities Officer',                        'Handles day-to-day facilities maintenance and services'],
        ['HSE Manager',                               'Leads health, safety and environmental compliance programme'],
        ['HSE Officer',                               'Implements HSE policies and monitors workplace safety'],
        ['Safety Officer',                            'Ensures compliance with occupational health and safety standards'],

        // ── Project & Programme Management ───────────────────────────────────
        ['Programme Director',                        'Leads a portfolio of related projects to deliver strategic outcomes'],
        ['Programme Manager',                         'Manages a programme of projects and interdependencies'],
        ['Senior Project Manager',                    'Leads complex or high-value projects'],
        ['Project Manager',                           'Plans, executes and closes projects on time and within budget'],
        ['Junior Project Manager',                    'Supports project management activities under supervision'],
        ['Project Coordinator',                       'Coordinates project tasks, schedules and documentation'],
        ['Project Analyst',                           'Analyses project data and provides reporting support'],
        ['Project Administrator',                     'Provides administrative support to the project team'],
        ['PMO Director',                              'Leads the Project Management Office'],
        ['PMO Manager',                               'Manages PMO standards, governance and project portfolio'],
        ['PMO Analyst',                               'Supports PMO reporting, tools and governance'],
        ['Change Manager',                            'Manages organisational change initiatives and stakeholder adoption'],

        // ── Supply Chain, Logistics & Procurement ────────────────────────────
        ['Supply Chain Director',                     'Leads end-to-end supply chain strategy and operations'],
        ['Supply Chain Manager',                      'Manages procurement, inventory and distribution activities'],
        ['Supply Chain Analyst',                      'Analyses supply chain data and optimises processes'],
        ['Procurement Director',                      'Leads strategic sourcing and procurement governance'],
        ['Procurement Manager',                       'Manages procurement operations and supplier relationships'],
        ['Procurement Officer',                       'Handles purchasing activities and supplier coordination'],
        ['Procurement Coordinator',                   'Coordinates purchase orders and procurement administration'],
        ['Senior Buyer',                              'Manages key supplier negotiations and category spend'],
        ['Buyer',                                     'Sources and purchases goods and services for the organisation'],
        ['Category Manager',                          'Manages spend within a defined category of goods or services'],
        ['Vendor Relations Manager',                  'Manages supplier partnerships and performance'],
        ['Demand Planner',                            'Forecasts demand and ensures supply chain alignment'],
        ['Inventory Manager',                         'Manages stock levels, replenishment and inventory accuracy'],
        ['Inventory Controller',                      'Monitors and controls inventory movement and records'],
        ['Warehouse Manager',                         'Manages warehouse operations, staff and storage systems'],
        ['Warehouse Supervisor',                      'Supervises warehouse staff and daily operations'],
        ['Warehouse Officer',                         'Handles receiving, storage and dispatch of goods'],
        ['Storekeeper',                               'Maintains store records and manages stock movements'],
        ['Stores Clerk',                              'Processes stock entries, requisitions and issue notes'],
        ['Logistics Manager',                         'Manages transportation, distribution and delivery networks'],
        ['Logistics Coordinator',                     'Coordinates logistics movements and freight bookings'],
        ['Transport Manager',                         'Manages vehicle fleet, transport routes and drivers'],
        ['Transport Officer',                         'Coordinates vehicle scheduling and transport operations'],
        ['Fleet Manager',                             'Manages fleet acquisition, maintenance and compliance'],
        ['Fleet Officer',                             'Handles day-to-day fleet administration and tracking'],
        ['Driver',                                    'Transports goods, equipment or personnel safely'],
        ['Customs & Clearing Agent',                  'Manages import/export documentation and customs clearance'],
        ['Freight Forwarder',                         'Arranges international shipment of goods on behalf of clients'],

        // ── Quality Assurance & Control ───────────────────────────────────────
        ['Quality Director',                          'Leads quality strategy and management systems across the organisation'],
        ['Quality Manager',                           'Manages the quality management system and audit programme'],
        ['Quality Engineer',                          'Designs quality processes and investigates quality failures'],
        ['Quality Analyst',                           'Analyses quality data and process performance'],
        ['Quality Assurance Officer',                 'Ensures processes meet quality and compliance standards'],
        ['Quality Control Inspector',                 'Inspects products or outputs against specifications'],
        ['Quality Auditor',                           'Conducts internal and supplier quality audits'],
        ['ISO & Standards Manager',                   'Manages ISO certification and standards compliance'],
        ['Lab Manager',                               'Manages laboratory operations, staff and compliance'],
        ['Laboratory Scientist / Analyst',            'Conducts scientific tests and analyses in the laboratory'],
        ['Calibration Technician',                    'Maintains calibration of measuring and testing equipment'],

        // ── Administration ────────────────────────────────────────────────────
        ['Administrative Director',                   'Leads administrative and office management functions'],
        ['Administrative Manager',                    'Manages administrative operations and support services'],
        ['Office Manager',                            'Oversees day-to-day office operations and administration'],
        ['Senior Administrative Officer',             'Handles complex administrative tasks and process management'],
        ['Administrative Officer',                    'Provides general administrative support across the organisation'],
        ['Administrative Assistant',                  'Provides day-to-day administrative and clerical support'],
        ['Executive Assistant',                       'Provides high-level personal and administrative support to executives'],
        ['Personal Assistant (PA)',                   'Provides confidential secretarial and administrative support to a senior individual'],
        ['Secretary',                                 'Provides clerical and secretarial support including correspondence and scheduling'],
        ['Senior Receptionist',                       'Manages front-desk operations and supervises reception staff'],
        ['Receptionist / Front Desk Officer',         'Manages the reception area and handles visitors and calls'],
        ['Office Assistant',                          'Provides general office support including filing and errands'],
        ['Records Manager',                           'Manages the organisational records and archives programme'],
        ['Records Officer',                           'Maintains physical and digital records and filing systems'],
        ['Document Controller',                       'Controls document versions, distribution and archiving'],
        ['Travel Coordinator',                        'Arranges travel, accommodation and logistics for staff'],

        // ── Healthcare ────────────────────────────────────────────────────────
        ['Medical Director / Chief Medical Officer',  'Leads clinical strategy, quality and medical governance'],
        ['Specialist Consultant (Medicine)',          'Provides specialist medical opinion and treatment in a defined field'],
        ['Physician / Medical Doctor',                'Provides diagnosis, treatment and clinical care to patients'],
        ['General Practitioner (GP)',                 'Provides primary healthcare and referrals'],
        ['Surgeon',                                   'Performs surgical procedures in a specialised discipline'],
        ['Registrar (Medicine)',                      'Specialist trainee doctor in a hospital setting'],
        ['Medical Officer',                           'Provides clinical services under supervision'],
        ['House Officer / Intern Doctor',             'Junior doctor in the first year of post-graduate training'],
        ['Nurse Practitioner',                        'Advanced practice nurse with expanded clinical authority'],
        ['Chief Nursing Officer',                     'Leads nursing strategy, standards and workforce'],
        ['Senior Nurse / Charge Nurse',               'Leads nursing care on a ward or unit'],
        ['Registered Nurse (RN)',                     'Qualified nurse providing direct patient care'],
        ['Enrolled Nurse (EN)',                       'Support nurse working under the supervision of a registered nurse'],
        ['Midwife',                                   'Provides maternal and newborn care during pregnancy and birth'],
        ['Clinical Officer',                          'Provides clinical diagnosis and treatment; common in East Africa'],
        ['Pharmacist',                                'Manages medication dispensing, counselling and pharmacy operations'],
        ['Senior Pharmacist',                         'Leads pharmacy operations or a specialist pharmacy area'],
        ['Pharmacy Technician',                       'Assists pharmacists in dispensing and medication management'],
        ['Medical Laboratory Scientist',              'Conducts diagnostic laboratory tests and analyses'],
        ['Radiographer',                              'Operates imaging equipment and interprets radiological images'],
        ['Physiotherapist',                           'Provides rehabilitation and physical therapy to patients'],
        ['Occupational Therapist',                    'Helps patients develop or recover daily living and working skills'],
        ['Nutritionist / Registered Dietitian',      'Advises on diet, nutrition and therapeutic meal planning'],
        ['Clinical Psychologist',                     'Assesses and treats mental health conditions'],
        ['Psychiatrist',                              'Medical doctor specialising in mental health disorders'],
        ['Dental Surgeon / Dentist',                  'Provides oral health and dental treatment'],
        ['Optometrist',                               'Examines eyes and prescribes corrective lenses'],
        ['Biomedical Technologist',                   'Maintains and troubleshoots medical equipment'],
        ['Medical Records Officer',                   'Manages patient records and health information systems'],
        ['Community Health Worker',                   'Provides basic health education and referrals in communities'],

        // ── Education & Academic ──────────────────────────────────────────────
        ['Vice Chancellor / Chancellor',              'Head of a university or higher education institution'],
        ['Principal',                                 'Head of a school, college or training institution'],
        ['Deputy Principal',                          'Second-in-command to the Principal'],
        ['Dean of Faculty',                           'Academic head of a faculty or school within a university'],
        ['Head of Department (Academic)',             'Leads an academic department or programme'],
        ['Professor',                                 'Senior academic with significant research and teaching credentials'],
        ['Associate Professor',                       'Mid-senior academic between lecturer and professor level'],
        ['Senior Lecturer',                           'Experienced academic with teaching, research and supervision duties'],
        ['Lecturer',                                  'Academic staff responsible for teaching and research'],
        ['Assistant Lecturer',                        'Junior academic supporting teaching and research activities'],
        ['Tutorial Fellow',                           'Early-career academic providing tutorials and teaching support'],
        ['Research Fellow',                           'Conducts independent research within an academic or research institution'],
        ['Teacher / Educator',                        'Delivers teaching and learning at primary or secondary level'],
        ['Senior Teacher',                            'Experienced teacher with additional pastoral or leadership duties'],
        ['Pre-School Teacher',                        'Delivers early childhood education and care'],
        ['School Counsellor',                         'Provides academic, career and personal counselling to students'],
        ['Librarian',                                 'Manages library collections, information services and research support'],
        ['Curriculum Specialist',                     'Develops and reviews academic curricula and learning frameworks'],
        ['Examinations Officer',                      'Manages examination administration, scheduling and results'],
        ['Academic Registrar',                        'Manages student records, enrolment and academic administration'],
        ['Laboratory Technician (Academic)',          'Supports practical laboratory sessions in an academic environment'],

        // ── Hospitality & Tourism ─────────────────────────────────────────────
        ['General Manager (Hotel)',                   'Overall management of a hotel or hospitality property'],
        ['Rooms Division Manager',                    'Oversees front office, housekeeping and guest experience departments'],
        ['Front Office Manager',                      'Manages front desk, reservations and guest check-in/out'],
        ['Reservations Manager',                      'Oversees room reservations, yield and occupancy management'],
        ['Guest Relations Manager',                   'Manages VIP guests and overall guest satisfaction'],
        ['Guest Relations Officer',                   'Provides personalised service to guests during their stay'],
        ['Concierge',                                 'Provides personalised assistance, tours, bookings and local recommendations to guests'],
        ['Front Desk Agent',                          'Handles guest check-in, check-out and enquiries at the front desk'],
        ['Revenue Manager',                           'Optimises room rates and occupancy through dynamic pricing'],
        ['Night Auditor',                             'Processes end-of-day financial transactions and audit checks'],
        ['Housekeeping Manager',                      'Leads housekeeping operations and cleaning standards'],
        ['Housekeeping Supervisor',                   'Supervises room attendants and housekeeping assignments'],
        ['Room Attendant / Housekeeper',              'Cleans and prepares guest rooms to hotel standards'],
        ['Laundry Supervisor',                        'Manages laundry operations for linen and uniforms'],
        ['Food & Beverage Manager',                   'Oversees all food and beverage outlets and service standards'],
        ['Restaurant Manager',                        'Manages a restaurant or dining outlet'],
        ['Bar Manager',                               'Manages bar operations, stock and service'],
        ['Executive Chef / Head Chef',                'Leads the kitchen brigade, menu creation and culinary standards'],
        ['Sous Chef',                                 'Second-in-command in the kitchen; deputises for the Head Chef'],
        ['Chef de Partie',                            'Leads a specific section of the kitchen'],
        ['Cook',                                      'Prepares and cooks food under the supervision of the chef team'],
        ['Kitchen Steward',                           'Maintains kitchen cleanliness, equipment and dishwashing'],
        ['Waiter / Waitress',                         'Provides table service in a restaurant or event setting'],
        ['Bartender',                                 'Prepares and serves cocktails and beverages'],
        ['Events & Banqueting Manager',               'Plans and executes events, conferences and banquets'],
        ['Events Coordinator (Hospitality)',          'Coordinates event logistics including setup, catering and AV'],
        ['Spa Manager',                               'Manages spa operations, therapists and wellness programmes'],
        ['Spa Therapist',                             'Delivers spa treatments and wellness services to guests'],
        ['Tour & Travel Consultant',                  'Plans and books travel itineraries and tours for clients'],

        // ── Agriculture ───────────────────────────────────────────────────────
        ['Farm Manager / Farm Director',              'Overall management of farm operations and production targets'],
        ['Senior Agronomist',                         'Provides expert agronomy advice and leads crop improvement programmes'],
        ['Agronomist',                                'Advises on crop production, soil management and pest control'],
        ['Agricultural Officer',                      'Implements agricultural programmes and provides field support'],
        ['Extension Officer',                         'Provides training and advisory services to smallholder farmers'],
        ['Livestock Manager',                         'Manages cattle, poultry, dairy or other livestock operations'],
        ['Veterinary Officer / Veterinarian',         'Provides animal health services, treatment and disease management'],
        ['Irrigation Engineer',                       'Designs and manages irrigation infrastructure and water use'],
        ['Agricultural Engineer',                     'Maintains and operates farm machinery and equipment'],
        ['Farm Supervisor',                           'Supervises field workers and day-to-day farm activities'],
        ['Post-Harvest Officer',                      'Manages crop handling, storage and grading after harvest'],
        ['Farm Worker / Agricultural Labourer',       'Carries out planting, weeding, harvesting and general farm tasks'],

        // ── Construction & Real Estate ────────────────────────────────────────
        ['Construction Manager',                      'Manages the planning, execution and completion of construction projects'],
        ['Quantity Surveyor',                         'Manages construction costs from feasibility through to completion'],
        ['Senior Quantity Surveyor',                  'Leads cost management on large or complex construction projects'],
        ['Site Engineer',                             'Provides on-site technical supervision and quality control'],
        ['Architect',                                 'Designs buildings and spaces; oversees planning and specification'],
        ['Urban & Regional Planner',                  'Plans land use, urban development and zoning'],
        ['M&E Engineer',                              'Designs and manages mechanical, electrical and plumbing systems'],
        ['Safety Officer (Construction)',             'Enforces health and safety standards on construction sites'],
        ['Foreman',                                   'Supervises and coordinates trade workers on site'],
        ['Contracts Manager (Construction)',          'Manages construction contracts and commercial risk'],
        ['Property Manager',                          'Manages rental properties, tenant relations and maintenance'],
        ['Property Sales Executive',                  'Markets and sells properties to buyers or tenants'],
        ['Property Valuer',                           'Assesses the market value of properties'],
        ['Mason / Bricklayer',                        'Lays bricks, blocks and stones in construction work'],
        ['Electrician',                               'Installs, maintains and repairs electrical systems and wiring'],
        ['Plumber',                                   'Installs and maintains water, gas and drainage systems'],
        ['Carpenter / Joiner',                        'Constructs and installs timber structures, furniture and fittings'],
        ['Welder / Fabricator',                       'Joins metal components using welding and fabrication techniques'],
        ['Painter & Decorator',                       'Applies paint, varnish and decorative finishes to buildings'],
        ['General Labourer',                          'Performs manual construction tasks under the supervision of skilled tradespeople'],

        // ── Security ──────────────────────────────────────────────────────────
        ['Security Director',                         'Leads the organisations security strategy and operations'],
        ['Security Manager',                          'Manages security personnel, systems and risk assessments'],
        ['Security Supervisor',                       'Supervises security guards and shift operations'],
        ['Security Officer / Guard',                  'Provides on-site physical security and access control'],
        ['Loss Prevention Manager',                   'Leads programmes to prevent shrinkage, theft and fraud'],
        ['Loss Prevention Officer',                   'Monitors for and investigates loss prevention incidents'],
        ['Investigation Officer',                     'Conducts investigations into fraud, theft or misconduct'],
        ['Access Control Officer',                    'Manages entry/exit points and identity verification'],
        ['CCTV Operator',                             'Monitors CCTV systems and reports incidents'],

        // ── Manufacturing & Production ────────────────────────────────────────
        ['Production Director',                       'Leads manufacturing and production strategy'],
        ['Plant Manager',                             'Manages an entire manufacturing plant or production facility'],
        ['Production Manager',                        'Manages production schedules, teams and output targets'],
        ['Production Supervisor',                     'Supervises production line staff and shift operations'],
        ['Production Planner',                        'Plans production schedules to meet demand and capacity'],
        ['Machine Operator',                          'Operates machinery and equipment on the production floor'],
        ['Production Technician',                     'Performs technical tasks to support manufacturing operations'],
        ['Packaging Supervisor',                      'Supervises packaging lines and output quality'],
        ['Packaging Operator',                        'Operates packaging machinery and prepares finished goods'],
        ['Quality Control Technician',                'Inspects in-process and finished products against quality specifications'],

        // ── Media & Entertainment ─────────────────────────────────────────────
        ['Editor / Senior Editor',                    'Oversees content production, editorial standards and publishing workflow'],
        ['Journalist / Reporter',                     'Researches and writes news, features and investigative content'],
        ['Broadcast Journalist',                      'Presents or produces content for television or radio platforms'],
        ['Photographer / Videographer',               'Captures and produces visual content for media and marketing'],
        ['Scriptwriter / Screenwriter',               'Writes scripts for film, television, radio or digital content'],
        ['Producer (Media)',                          'Manages production of media content from concept to delivery'],
        ['Director (Film / TV)',                      'Directs filming and creative execution of media productions'],
        ['Presenter / Anchor',                        'Hosts news broadcasts, shows or live events on air'],
        ['Radio Host / DJ',                           'Hosts radio programmes and manages on-air content'],
        ['Animator',                                  'Creates 2D or 3D animation for digital or broadcast media'],
        ['Sound Engineer',                            'Records, mixes and produces audio for media and live events'],
        ['Content Creator / Digital Creator',        'Produces digital content for social media and online platforms'],

        // ── NGO & Social Services ─────────────────────────────────────────────
        ['Executive Director (NGO)',                  'Leads an NGO or non-profit organisation'],
        ['Programme Director (NGO)',                  'Leads programme strategy and implementation'],
        ['Programme Manager (NGO)',                   'Manages specific programmes, budgets and partner relations'],
        ['Programme Officer',                         'Implements programme activities and monitors progress'],
        ['M&E Manager',                               'Leads monitoring and evaluation systems and impact reporting'],
        ['M&E Officer',                               'Implements M&E data collection, analysis and reporting'],
        ['Community Liaison Officer',                 'Acts as a link between the organisation and local communities'],
        ['Social Worker',                             'Provides casework, counselling and social support services'],
        ['Grants Manager',                            'Manages grant applications, compliance and donor reporting'],
        ['Fundraising Officer',                       'Identifies and develops fundraising opportunities'],
        ['Field Coordinator',                         'Coordinates field activities and community-level implementation'],

        // ── Retail ────────────────────────────────────────────────────────────
        ['Store Manager (Retail)',                    'Manages overall retail store operations, staff and sales'],
        ['Assistant Store Manager',                   'Supports the Store Manager in day-to-day store operations'],
        ['Floor Manager',                             'Manages the shop floor, staff assignments and customer experience'],
        ['Cashier / Teller',                          'Processes customer payments and handles cash transactions'],
        ['Sales Assistant',                           'Assists customers on the shop floor and supports sales'],
        ['Merchandise Controller',                    'Manages stock levels, replenishment and merchandise displays'],
        ['Buyer (Retail)',                            'Selects and purchases products for retail sale'],
        ['Visual Merchandiser',                       'Designs and implements product displays to drive sales'],

        // ── General / Entry Level ─────────────────────────────────────────────
        ['Graduate Trainee / Management Trainee',    'Entry-level professional on a structured graduate development programme'],
        ['Industrial Attaché / Intern',              'Student or recent graduate gaining practical work experience'],
        ['Apprentice',                                'Trainee learning a skilled trade or profession through on-the-job training'],
        ['Officer (General)',                         'General professional officer handling defined functional responsibilities'],
        ['Junior Officer',                            'Early-career professional supporting a functional area'],
        ['Clerk / Office Clerk',                      'Handles routine clerical tasks such as filing, data entry and correspondence'],
        ['Messenger / Office Attendant',             'Delivers documents and provides general errand and support services'],
        ['Cook / Canteen Attendant',                  'Prepares meals and manages canteen or staff kitchen operations'],
        ['Cleaner / Janitor / Custodian',             'Maintains cleanliness and hygiene of office or facility premises'],
        ['Casual Labourer / General Worker',          'Performs unskilled or semi-skilled tasks on a temporary basis'],
    ];

    foreach ($designations as [$name, $description]) {
        $CI->db->insert($p . 'hr_designations', [
            'name'         => $name,
            'description'  => $description,
            'date_created' => $now,
        ]);
    }
}

// ── EMPLOYEE GROUPS (JOB GROUPS) ──────────────────────────────────────────────
if ($CI->db->count_all($p . 'hr_employee_groups') == 0) {

    $groups = [
        ['Executive Band',            'C-suite executives, board members and highest-ranking officers'],
        ['Senior Management',         'VPs, Senior Directors, Country Directors and General Managers'],
        ['Middle Management',         'Managers, Department Heads and Divisional Managers'],
        ['Supervisory',               'Team Leads, Supervisors, Coordinators and Senior Officers'],
        ['Senior Professional',       'Principal Officers, Senior Specialists and experienced individual contributors'],
        ['Professional',              'Qualified professionals — engineers, accountants, lawyers, analysts, nurses etc.'],
        ['Technical / Skilled',       'Technicians, tradespeople and skilled technical operators'],
        ['Clerical / Administrative', 'Administrative assistants, clerks, secretaries and office support staff'],
        ['Sales & Field Staff',       'Field sales representatives, agents and front-line commercial staff'],
        ['Semi-Skilled',              'Operators, drivers and workers with specific task-level training'],
        ['General / Support Staff',   'Cleaners, messengers, general labourers and unskilled support roles'],
        ['Temporary / Casual',        'Casual workers, seasonal employees and short-term contract staff'],
        ['Trainee / Intern',          'Graduate trainees, interns, apprentices and attachés'],
    ];

    foreach ($groups as [$name, $description]) {
        $CI->db->insert($p . 'hr_employee_groups', [
            'name'         => $name,
            'description'  => $description,
            'date_created' => $now,
        ]);
    }
}

// ── EMPLOYEE GRADES ───────────────────────────────────────────────────────────
if ($CI->db->count_all($p . 'hr_employee_grades') == 0) {

    // Generic salary-band grade table — override with local ranges after install
    $grades = [
        ['Grade EX — Executive',          0, 0],
        ['Grade A — Senior Management',   0, 0],
        ['Grade B — Management',          0, 0],
        ['Grade C — Supervisory',         0, 0],
        ['Grade D — Senior Professional', 0, 0],
        ['Grade E — Professional',        0, 0],
        ['Grade F — Technical',           0, 0],
        ['Grade G — Clerical',            0, 0],
        ['Grade H — Semi-Skilled',        0, 0],
        ['Grade I — General / Support',   0, 0],
        ['Grade T — Trainee / Intern',    0, 0],
    ];

    foreach ($grades as [$name, $min, $max]) {
        $CI->db->insert($p . 'hr_employee_grades', [
            'name'         => $name,
            'min_salary'   => $min,
            'max_salary'   => $max,
            'date_created' => $now,
        ]);
    }
}

// ── INTERVIEW TYPES ───────────────────────────────────────────────────────────
if ($CI->db->count_all($p . 'hr_interview_types') == 0) {
    $types = [
        ['Phone / Telephone Screen',
         'Initial telephone call to assess basic qualifications, communication skills and general fit with the role. Typically 15–30 minutes.'],
        ['Video Interview',
         'Remote video interview via Zoom, Microsoft Teams or Google Meet. Used for in-depth candidate assessment across geographic locations.'],
        ['In-Person / Face-to-Face Interview',
         'On-site interview at company premises. Allows for direct interaction and a deeper assessment of the candidate.'],
        ['Panel Interview',
         'Interview conducted simultaneously by a panel of 2–5 assessors from HR, line management and cross-functional teams.'],
        ['Competency-Based Interview (STAR)',
         'Structured behavioural interview using the STAR framework (Situation, Task, Action, Result) to assess specific competencies.'],
        ['Technical / Coding Test',
         'Written or computer-based assessment of role-specific technical knowledge, programming ability or domain expertise.'],
        ['Case Study Interview',
         'Candidate is given a real or simulated business problem to analyse and present a structured, data-driven solution.'],
        ['Presentation Round',
         'Candidate prepares and delivers a presentation on an assigned topic or business proposal to the selection panel.'],
        ['Psychometric / Aptitude Assessment',
         'Standardised tests measuring cognitive ability (numerical, verbal, abstract reasoning), personality traits and work-style preferences.'],
        ['Group Assessment / Assessment Centre',
         'Structured full or half-day event with group discussions, role plays, in-tray exercises and individual tasks observed by assessors.'],
        ['Work Sample / Practical Test',
         'Candidate completes a realistic work-related task or simulation that mirrors actual job responsibilities.'],
        ['Portfolio Review',
         'Review and structured discussion of the candidate\'s previous work, professional projects, publications or creative portfolio.'],
        ['Background & Reference Check',
         'Verification of employment history, academic credentials, professional licences and structured interviews with provided referees.'],
        ['Medical / Fitness Assessment',
         'Pre-employment health screening, physical fitness evaluation or occupational health assessment required for specific roles.'],
        ['Stress Interview',
         'A deliberately challenging and pressurised interview designed to evaluate composure, resilience and problem-solving under pressure.'],
    ];
    foreach ($types as [$name, $desc]) {
        $CI->db->insert($p . 'hr_interview_types', ['name' => $name, 'description' => $desc]);
    }
}

// ── INTERVIEW ROUNDS ──────────────────────────────────────────────────────────
if ($CI->db->count_all($p . 'hr_interview_rounds') == 0) {
    $rounds = [
        ['Round 0 – Application Screening',
         'Initial shortlisting of CVs, cover letters and application forms by the HR or Talent Acquisition team. No candidate interaction.'],
        ['Round 1 – HR Screening Call',
         'Brief phone or video call with HR to verify basic qualifications, confirm role interest and communicate the process.'],
        ['Round 2 – Hiring Manager Interview',
         'In-depth interview with the direct line manager to assess role fit, experience and technical capabilities.'],
        ['Round 3 – Technical Assessment',
         'Role-specific technical test, coding challenge, written exam or practical exercise to evaluate domain knowledge.'],
        ['Round 4 – Department Panel Interview',
         'Panel interview with team members, department head and cross-functional stakeholders to assess team fit and broader competencies.'],
        ['Round 5 – Presentation Round',
         'Candidate delivers a prepared presentation on an assigned topic to the full selection panel.'],
        ['Round 6 – Psychometric & Aptitude Testing',
         'Standardised cognitive ability, personality profiling and behavioural assessments administered online or in-person.'],
        ['Round 7 – Assessment Centre',
         'Full or half-day structured assessment involving group exercises, case studies, role plays and individual tasks.'],
        ['Round 8 – Senior / Executive Interview',
         'Final interview with a senior leader, CEO, MD or board member for strategic fit and leadership alignment.'],
        ['Round 9 – Background & Reference Checks',
         'Verification of employment history, academic credentials, professional registrations and structured referee interviews.'],
        ['Round 10 – Pre-Employment Medical',
         'Occupational health screening and fitness assessment required before the offer of employment is formalised.'],
        ['Offer Discussion & Negotiation',
         'Compensation package presentation, benefits discussion and negotiation with the successful candidate. Closes the recruitment cycle.'],
    ];
    foreach ($rounds as [$name, $desc]) {
        $CI->db->insert($p . 'hr_interview_rounds', ['name' => $name, 'description' => $desc]);
    }
}

// ── APPOINTMENT LETTER TEMPLATES ──────────────────────────────────────────────
if ($CI->db->count_all($p . 'hr_appointment_letter_templates') == 0) {

    // Ensure extra columns exist (added by inline migration in controller; add here too for seed runs at install time)
    foreach (['introduction' => "TEXT DEFAULT NULL AFTER `content`", 'closing_statement' => "TEXT DEFAULT NULL AFTER `introduction`"] as $_col => $_def) {
        if (!$CI->db->field_exists($_col, $p . 'hr_appointment_letter_templates')) {
            $CI->db->query("ALTER TABLE `{$p}hr_appointment_letter_templates` ADD COLUMN `{$_col}` {$_def}");
        }
    }

    // Ensure terms table exists
    if (!$CI->db->table_exists($p . 'hr_appointment_letter_template_terms')) {
        $CI->db->query("CREATE TABLE `{$p}hr_appointment_letter_template_terms` (
            `id`          INT(11) NOT NULL AUTO_INCREMENT,
            `template_id` INT(11) NOT NULL,
            `title`       VARCHAR(300) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `sort_order`  INT(5) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `template_id` (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    // ── Helper: insert template + its standard terms ──────────────────────────
    $insert_template = function($name, $intro, $content, $closing, $terms) use ($CI, $p, $now) {
        $CI->db->insert($p . 'hr_appointment_letter_templates', [
            'name'             => $name,
            'introduction'     => $intro,
            'content'          => $content,
            'closing_statement'=> $closing,
            'date_created'     => $now,
        ]);
        $tid = $CI->db->insert_id();
        foreach ($terms as $i => [$title, $desc]) {
            $CI->db->insert($p . 'hr_appointment_letter_template_terms', [
                'template_id' => $tid,
                'title'       => $title,
                'description' => $desc,
                'sort_order'  => $i + 1,
            ]);
        }
    };

    $std_intro   = "Dear {applicant_name},\n\nFollowing your successful application and the recent selection process, we are pleased to offer you employment at {company_name} on the following terms and conditions.";
    $std_closing = "Please sign and return a copy of this letter to confirm your acceptance of these terms within seven (7) days of the date of this letter. We look forward to welcoming you to the team and trust this will be the beginning of a mutually rewarding association.\n\nYours sincerely,\n\n_______________________________\n{hr_manager_name}\nHuman Resources Department\n{company_name}";

    // Standard terms used by most templates
    $std_terms = [
        ['Commencement Date',       'Your employment commences on {joining_date} unless otherwise agreed in writing.'],
        ['Job Title',               'You are appointed to the position of {designation}.'],
        ['Department & Location',   'You will work in the {department} department, based at {location}. The Company reserves the right to transfer you to any department or location as operational requirements demand.'],
        ['Reporting Line',          'You will report to {manager_name} or such other person as the Company may designate from time to time.'],
        ['Gross Monthly Salary',    'Your gross monthly salary is {currency} {salary}, payable on or before the last working day of each calendar month.'],
        ['Payment Method',          'Salary shall be remitted directly to the bank account or mobile money number you provide to the HR department.'],
        ['Normal Working Hours',    'Normal working hours are {working_hours_per_day} hours per day, {work_days_per_week} days per week. You may be required to work outside normal hours as operational needs demand without additional compensation unless the Company\'s overtime policy applies.'],
        ['Probationary Period',     'This appointment is subject to a probationary period of {probation_months} months commencing on your start date, during which either party may terminate the contract by giving {probation_notice_days} days\' written notice.'],
        ['Confirmation of Employment', 'Upon successful completion of the probationary period, your employment shall be confirmed in writing by HR.'],
        ['Notice Period (Post-Confirmation)', 'After confirmation, either party wishing to terminate this contract shall give {notice_months} months\' written notice or equivalent salary in lieu of notice.'],
        ['Annual Leave',            'You are entitled to {leave_days} working days of paid annual leave per leave year, to be taken at times mutually agreed with your line manager.'],
        ['Medical Cover',           'The Company provides medical insurance cover as per the current Company Medical Scheme. Details of the scheme and dependant eligibility will be shared upon joining.'],
        ['Confidentiality',         'You shall at all times maintain strict confidentiality of all company information, trade secrets, client data and any other proprietary information you access in the course of your employment, both during and after your employment.'],
        ['Company Property',        'All equipment, documents, software and intellectual property produced in the course of your employment remain the sole property of {company_name}.'],
        ['Code of Conduct',         'Your employment is subject to the Company\'s Employee Handbook, Code of Conduct, HR Policies and any other policies and procedures as may be issued from time to time.'],
        ['Governing Law',           'This letter of appointment is governed by the laws of {country} and any disputes shall be resolved in accordance with applicable employment legislation.'],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Template 1 — General Staff Appointment
    // ─────────────────────────────────────────────────────────────────────────
    $insert_template(
        'General Staff Appointment',
        $std_intro,
        "<p>You will be responsible for carrying out the duties and responsibilities associated with the role of <strong>{designation}</strong> as communicated during the selection process and as may be expanded upon in your job description, which forms part of this offer.</p>\n<p>The Company may, at its discretion, assign you additional responsibilities commensurate with your skills and grade from time to time.</p>",
        $std_closing,
        $std_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 2 — Senior Management / Executive Appointment
    // ─────────────────────────────────────────────────────────────────────────
    $exec_terms = array_merge($std_terms, [
        ['Performance & KPIs',       'Your performance will be assessed against Key Performance Indicators (KPIs) agreed annually with the Board / CEO. A formal performance review shall be conducted every {review_period}.'],
        ['Leadership Responsibilities', 'As a member of the senior management team, you are expected to lead your function, contribute to the Company\'s strategic direction and model the Company\'s values and culture.'],
        ['Relocation Allowance',     'Where applicable, the Company will provide a one-off relocation allowance of {currency} {relocation_amount} subject to the terms of the Company\'s Relocation Policy.'],
        ['Motor Vehicle Benefit',    'You are entitled to a motor vehicle benefit / car allowance of {currency} {car_allowance} per month, subject to the Company\'s Vehicle Policy.'],
        ['Non-Compete Clause',       'During the term of your employment and for a period of {noncompete_period} after cessation, you shall not engage in or facilitate any business that directly competes with {company_name} without prior written consent from the Board.'],
    ]);
    $insert_template(
        'Senior Management / Executive Appointment',
        "Dear {applicant_name},\n\nThe Board of Directors / Management of {company_name} is pleased to appoint you to a senior leadership position, subject to the terms and conditions below. This letter supersedes all prior verbal or written communications regarding this appointment.",
        "<p>In the role of <strong>{designation}</strong>, you will provide strategic leadership and operational oversight for your assigned function. You will be expected to drive the execution of the Company's business strategy, manage stakeholder relationships, and deliver measurable business outcomes.</p>\n<p>You will be a member of the Senior Management Team and attend all relevant executive, board and governance meetings as required.</p>",
        $std_closing,
        $exec_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 3 — Information Technology Department
    // ─────────────────────────────────────────────────────────────────────────
    $it_terms = array_merge($std_terms, [
        ['Intellectual Property',    'All software, code, algorithms, databases, designs, documentation and any other works created in the course of your duties are the exclusive intellectual property of {company_name} and you hereby assign all rights thereto to the Company.'],
        ['Information Security',     'You will comply at all times with the Company\'s Information Security Policy, including the acceptable use policy for all IT systems, devices and data.'],
        ['Remote / Hybrid Working',  'Subject to management approval, you may work remotely on agreed days. Remote working is contingent on meeting performance standards and maintaining adequate information security.'],
        ['Technology Allowance',     'Where applicable, a technology allowance of {currency} {tech_allowance} per month is provided for mobile data and equipment maintenance.'],
        ['On-Call / Standby',        'Your role may require you to be on-call or standby duty on a rotational basis. Specific allowances for on-call duty are detailed in the IT Department\'s On-Call Policy.'],
    ]);
    $insert_template(
        'Information Technology Department',
        $std_intro,
        "<p>As a member of the <strong>Information Technology</strong> team, you will be responsible for the design, development, implementation, maintenance and support of technology systems that enable the Company's operations and strategic objectives.</p>\n<p>Your role of <strong>{designation}</strong> requires you to apply your technical expertise to deliver high-quality, secure and scalable technology solutions while adhering to industry best practices and the Company's IT standards.</p>",
        $std_closing,
        $it_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 4 — Sales & Business Development
    // ─────────────────────────────────────────────────────────────────────────
    $sales_terms = array_merge($std_terms, [
        ['Sales Targets',            'You will be assigned individual or team revenue targets reviewed on a {target_cycle} basis. Targets will be communicated in writing by your Sales Manager.'],
        ['Commission / Incentive',   'In addition to your basic salary, you are eligible for a sales commission / incentive as detailed in the separate Sales Incentive Scheme document, which may be amended by the Company from time to time.'],
        ['Territory / Accounts',     'You will be assigned a territory, industry vertical or set of accounts as communicated by your line manager. Assignment of accounts may be adjusted as business requirements change.'],
        ['Travel & Expenses',        'You will be reimbursed for approved business travel and client entertainment expenses incurred in the performance of your duties, in accordance with the Company\'s Travel & Expense Policy.'],
        ['Non-Solicitation',         'During your employment and for {nonsolicit_period} after cessation, you shall not solicit or attempt to solicit any clients, prospects or employees of {company_name} for the benefit of any competing business.'],
    ]);
    $insert_template(
        'Sales & Business Development',
        $std_intro,
        "<p>As <strong>{designation}</strong> in the Sales & Business Development team, you will be responsible for identifying, pursuing and closing new business opportunities, managing client relationships and contributing to the revenue growth of {company_name}.</p>\n<p>You are expected to consistently achieve or exceed assigned sales targets, build a strong pipeline, represent the Company professionally in all client interactions and provide accurate sales forecasts and reports.</p>",
        $std_closing,
        $sales_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 5 — Finance & Accounts
    // ─────────────────────────────────────────────────────────────────────────
    $finance_terms = array_merge($std_terms, [
        ['Financial Confidentiality', 'You acknowledge that you will have access to highly sensitive financial information. You are strictly prohibited from disclosing any financial data, forecasts, client balances or transactional information to any unauthorised party.'],
        ['Professional Registration', 'Where your role requires membership of a professional accounting body (e.g. ICPAK, ACCA, CPA, CIMA), it is your responsibility to maintain a valid and current professional registration throughout your employment.'],
        ['Fidelity Bond / Surety',   'Given the financial nature of your role, you may be required to execute a fidelity bond or provide a personal surety as a condition of employment, as determined by the Company.'],
        ['Tax & Statutory Compliance','You are expected to maintain up-to-date knowledge of applicable tax legislation, IFRS/IAS accounting standards and all relevant statutory reporting requirements in force in {country}.'],
    ]);
    $insert_template(
        'Finance & Accounts',
        $std_intro,
        "<p>As <strong>{designation}</strong> in the Finance & Accounts department, you will be responsible for maintaining accurate financial records, preparing timely and accurate reports, ensuring statutory compliance and supporting sound financial decision-making across the organisation.</p>\n<p>You are expected to uphold the highest standards of financial integrity, professional ethics and accuracy in all aspects of your work.</p>",
        $std_closing,
        $finance_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 6 — Human Resources
    // ─────────────────────────────────────────────────────────────────────────
    $hr_terms = array_merge($std_terms, [
        ['HR Confidentiality',       'Your role gives you access to sensitive employee personal data, compensation information and disciplinary records. You are bound by the strictest confidentiality obligations and must comply with all applicable data protection legislation.'],
        ['HR Ethics',                'You are expected to treat all employees with fairness, dignity and impartiality and to apply HR policies consistently and without bias.'],
        ['Professional Membership',  'Membership of a recognised HR professional body (e.g. IHRM, SHRM, CIPD) is encouraged and may be required for certain roles. The Company will support active professional development in this area.'],
    ]);
    $insert_template(
        'Human Resources',
        $std_intro,
        "<p>As <strong>{designation}</strong> in the Human Resources department, you will be responsible for supporting the full employee lifecycle — from talent acquisition and onboarding through to performance management, learning & development, employee relations and offboarding.</p>\n<p>You will be a trusted custodian of employee data and a champion of the Company's culture, values and people policies.</p>",
        $std_closing,
        $hr_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 7 — Healthcare / Medical Staff
    // ─────────────────────────────────────────────────────────────────────────
    $med_terms = array_merge($std_terms, [
        ['Professional Registration', 'Your appointment is conditional upon possession of and maintaining a valid professional licence / registration with the relevant regulatory body (e.g. Kenya Medical Practitioners and Dentists Council, Nursing Council of Kenya or equivalent). Failure to maintain registration will render this offer null and void.'],
        ['Clinical Governance',      'You will practise in accordance with the clinical governance framework, protocols and standard operating procedures of {company_name} and all applicable national clinical guidelines.'],
        ['Medical Indemnity',        'The Company provides professional indemnity cover for clinical practice conducted within the scope of your employment. Any private practice outside Company premises must be separately indemnified.'],
        ['Shift Work / On-Call',     'Your role requires you to work on a shift or on-call rotational basis as determined by the departmental roster. Specific allowances for night shift and on-call duty are detailed in the Medical Staff Policy.'],
        ['Continuing Professional Development (CPD)', 'You are expected to maintain CPD activities as required by your professional body and as supported by the Company\'s L&D programme.'],
    ]);
    $insert_template(
        'Healthcare / Medical Staff',
        "Dear {applicant_name},\n\n{company_name} is pleased to offer you an appointment in a healthcare role, subject to the terms below and the successful verification of your professional registration and qualifications.",
        "<p>As <strong>{designation}</strong>, you will provide professional healthcare services to patients, clients or staff in accordance with your clinical scope of practice, the Company's clinical protocols and all applicable health regulations.</p>\n<p>You are expected to uphold the highest standards of clinical care, patient safety, professional ethics and record-keeping at all times.</p>",
        $std_closing,
        $med_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 8 — Operations & Logistics
    // ─────────────────────────────────────────────────────────────────────────
    $ops_terms = array_merge($std_terms, [
        ['Health & Safety',          'You are required to comply with all health, safety and environmental (HSE) policies and procedures and to report any unsafe conditions or incidents immediately to your supervisor or the HSE Officer.'],
        ['Shift Work',               'Your role may require you to work on a shift rotational basis including early morning, late evening or weekend shifts as communicated by your Operations Manager.'],
        ['Uniform & Personal Protective Equipment (PPE)', 'Where applicable, the Company will provide uniforms and/or PPE. You are required to wear the prescribed uniform and PPE at all times while on duty.'],
        ['Driving Licence',          'If your role requires driving Company or client vehicles, you must possess and maintain a valid driving licence appropriate for the vehicles operated. Any endorsements or licence revocations must be reported immediately to HR.'],
    ]);
    $insert_template(
        'Operations & Logistics',
        $std_intro,
        "<p>As <strong>{designation}</strong> in the Operations & Logistics team, you will be responsible for ensuring the efficient and safe movement, storage and delivery of goods, services or assets in accordance with operational plans and service level agreements.</p>\n<p>You are expected to uphold operational standards, maintain accurate records and contribute to the continuous improvement of operational processes.</p>",
        $std_closing,
        $ops_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 9 — Engineering & Technical Staff
    // ─────────────────────────────────────────────────────────────────────────
    $eng_terms = array_merge($std_terms, [
        ['Professional Engineering Registration', 'Where applicable, you are required to maintain registration with the relevant engineering regulatory body (e.g. Engineers Board of Kenya, Uganda Registration of Engineers). Failure to maintain registration may affect your continued employment.'],
        ['Health, Safety & Environment (HSE)',  'You are required to adhere to all HSE policies, wear appropriate PPE and immediately report any unsafe conditions, incidents or near-misses.'],
        ['Site Allowance',           'Where your role requires work on field sites, a site allowance of {currency} {site_allowance} per day on site will be provided in accordance with the Company\'s Site Policy.'],
        ['Intellectual Property',    'All designs, drawings, inventions, processes and technical works created in the course of your employment are the exclusive intellectual property of {company_name}.'],
        ['On-Call / Standby Duty',   'Your role may require on-call or standby duty on a rotational basis. Applicable allowances are detailed in the Engineering Department\'s On-Call Policy.'],
    ]);
    $insert_template(
        'Engineering & Technical Staff',
        $std_intro,
        "<p>As <strong>{designation}</strong> in the Engineering team, you will apply your technical expertise to design, build, maintain and optimise systems, equipment or infrastructure in accordance with the Company's technical standards, safety regulations and project requirements.</p>\n<p>You are expected to deliver high-quality engineering outcomes, maintain accurate technical documentation and continuously develop your professional competencies.</p>",
        $std_closing,
        $eng_terms
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Template 10 — Casual / Temporary Employment
    // ─────────────────────────────────────────────────────────────────────────
    $casual_terms = [
        ['Commencement & Duration',  'Your casual/temporary engagement commences on {joining_date} and is expected to last until {end_date}. This engagement may be terminated at any time by either party with {notice_days} days\' written notice or without notice in the event of gross misconduct.'],
        ['Role & Tasks',             'You are engaged as a {designation} on a casual/temporary basis to perform tasks as assigned by your supervisor from time to time.'],
        ['Hourly / Daily Rate',      'You will be remunerated at a rate of {currency} {rate} per {hour_or_day}, payable {payment_frequency}.'],
        ['Working Hours',            'Your working hours will be as assigned and may vary based on operational requirements. There is no guarantee of a minimum number of hours.'],
        ['No Employment Continuity', 'This casual/temporary engagement does not create an employment relationship, permanent contract, continuity of service or entitlement to statutory employment benefits unless required by applicable law.'],
        ['Statutory Deductions',     'All applicable statutory deductions (PAYE, NHIF/SHA, NSSF) will be made from your remuneration as required by law.'],
        ['Health & Safety',          'You are required to comply with all Company health and safety rules and to use any PPE provided.'],
        ['Confidentiality',          'You shall maintain the confidentiality of all company, client and operational information you access during this engagement.'],
        ['Code of Conduct',          'You are bound by the Company\'s Code of Conduct and Workplace Rules during the term of this engagement.'],
    ];
    $insert_template(
        'Casual / Temporary Employment',
        "Dear {applicant_name},\n\nWe are pleased to engage you on a casual/temporary basis at {company_name} on the terms below. This letter constitutes the full terms of your engagement.",
        "<p>You are engaged as <strong>{designation}</strong> on a casual/temporary basis. The Company makes no commitment to extend this engagement beyond the specified period. Should operational needs require, the Company may offer an extension in writing.</p>",
        "Please sign and return a copy of this engagement letter to confirm your acceptance. You are reminded that this engagement is temporary in nature and does not guarantee future permanent employment.\n\nYours sincerely,\n\n_______________________________\n{hr_manager_name}\nHuman Resources\n{company_name}",
        $casual_terms
    );
}

// ── LEAVE TYPES ───────────────────────────────────────────────────────────────
// Comprehensive list: Kenya statutory + East African regional + universal global.
// Idempotent — only inserts when the table is completely empty.
if ($CI->db->count_all($p . 'hr_leave_types') == 0) {

    /*
     * Schema reference:
     *   name, code, color, unit, is_paid, gender_restriction,
     *   requires_proof, allow_half_day, carry_forward, max_carry_forward,
     *   carry_forward_expiry_days, encashable, include_public_holidays,
     *   include_weekends, notice_days_required, max_consecutive_days,
     *   description, status
     */
    $leave_types = [

        // ── KENYA STATUTORY (Employment Act, 2007 + amendments) ──────────────

        [
            'name'                    => 'Annual Leave',
            'code'                    => 'AL',
            'color'                   => '#006b2c',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 1,
            'carry_forward'           => 1,
            'max_carry_forward'       => 10.00,
            'carry_forward_expiry_days' => 90,
            'encashable'              => 1,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 7,
            'max_consecutive_days'    => 0,
            'description'             => 'Statutory annual leave — minimum 21 working days per year under the Kenya Employment Act, 2007. Accrues at 1.75 days per month of service.',
        ],
        [
            'name'                    => 'Sick Leave',
            'code'                    => 'SL',
            'color'                   => '#dc2626',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 0,
            'max_consecutive_days'    => 0,
            'description'             => 'Statutory sick leave — 7 days fully paid + 7 days half pay per year (Employment Act s.30). A medical certificate is required for absences exceeding 2 consecutive days.',
        ],
        [
            'name'                    => 'Maternity Leave',
            'code'                    => 'ML',
            'color'                   => '#ec4899',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'female',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 30,
            'max_consecutive_days'    => 90,
            'description'             => 'Statutory maternity leave — 3 months (90 days) fully paid under the Kenya Employment Act, 2007 s.29. Requires a medical/hospital certificate confirming expected delivery date.',
        ],
        [
            'name'                    => 'Paternity Leave',
            'code'                    => 'PL',
            'color'                   => '#2563eb',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'male',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 7,
            'max_consecutive_days'    => 14,
            'description'             => 'Statutory paternity leave — 14 days fully paid for male employees following the birth of a child under the Kenya Employment (Amendment) Act, 2021.',
        ],
        [
            'name'                    => 'Compassionate / Bereavement Leave',
            'code'                    => 'BL',
            'color'                   => '#7c3aed',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 0,
            'max_consecutive_days'    => 5,
            'description'             => 'Paid leave granted on the death of an immediate family member (spouse, child, parent, sibling). Typically 3–5 days; a death certificate or funeral programme is required.',
        ],
        [
            'name'                    => 'Study / Examination Leave',
            'code'                    => 'EL',
            'color'                   => '#0891b2',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 14,
            'max_consecutive_days'    => 10,
            'description'             => 'Paid study or examination leave for employees pursuing approved courses of study. Requires proof of enrolment and examination timetable. Subject to management approval.',
        ],

        // ── EAST AFRICAN REGIONAL ─────────────────────────────────────────────

        [
            'name'                    => 'Adoption Leave',
            'code'                    => 'ADL',
            'color'                   => '#d97706',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 14,
            'max_consecutive_days'    => 30,
            'description'             => 'Paid leave granted to an employee who legally adopts a child. Duration mirrors primary caregiver entitlement. Legal adoption order is required.',
        ],
        [
            'name'                    => 'Marriage / Wedding Leave',
            'code'                    => 'WL',
            'color'                   => '#f59e0b',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 21,
            'max_consecutive_days'    => 5,
            'description'             => 'Paid leave granted once per employment on the occasion of the employee\'s legal marriage. Typically 3–5 days. Marriage certificate or official notice required.',
        ],
        [
            'name'                    => 'Religious / Cultural Observance Leave',
            'code'                    => 'RL',
            'color'                   => '#a855f7',
            'unit'                    => 'days',
            'is_paid'                 => 0,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 7,
            'max_consecutive_days'    => 3,
            'description'             => 'Unpaid or optionally paid leave for significant religious or cultural observances not covered by public holidays — e.g. Eid Al-Fitr, Eid Al-Adha, Diwali, Christmas Octave.',
        ],
        [
            'name'                    => 'Hajj / Pilgrimage Leave',
            'code'                    => 'HJL',
            'color'                   => '#16a34a',
            'unit'                    => 'days',
            'is_paid'                 => 0,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 30,
            'max_consecutive_days'    => 30,
            'description'             => 'Unpaid leave for Muslim employees performing the Hajj pilgrimage to Mecca. Granted once per employment. Requires hajj visa and travel booking confirmation.',
        ],

        // ── GLOBAL / UNIVERSAL ────────────────────────────────────────────────

        [
            'name'                    => 'Unpaid Leave',
            'code'                    => 'UPL',
            'color'                   => '#6b7280',
            'unit'                    => 'days',
            'is_paid'                 => 0,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 7,
            'max_consecutive_days'    => 90,
            'description'             => 'Leave without pay, granted at management discretion when all paid leave entitlements have been exhausted or for extended personal needs.',
        ],
        [
            'name'                    => 'Emergency / Force Majeure Leave',
            'code'                    => 'EMG',
            'color'                   => '#ef4444',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 0,
            'max_consecutive_days'    => 3,
            'description'             => 'Short paid leave for sudden unforeseen personal emergencies — e.g. flood, fire, road accident, hospitalization of a dependent. Evidence submitted within 48 hours.',
        ],
        [
            'name'                    => 'Work-From-Home Leave',
            'code'                    => 'WFH',
            'color'                   => '#0ea5e9',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 1,
            'max_consecutive_days'    => 0,
            'description'             => 'Approved remote working day(s). Employee remains fully productive from an off-site location. Subject to role suitability and manager approval.',
        ],
        [
            'name'                    => 'Compensatory Off (Comp-Off)',
            'code'                    => 'COMP',
            'color'                   => '#14b8a6',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 1,
            'carry_forward'           => 1,
            'max_carry_forward'       => 5.00,
            'carry_forward_expiry_days' => 90,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 1,
            'max_consecutive_days'    => 0,
            'description'             => 'Compensatory day off earned for working on a public holiday or weekend when not on shift. Must be utilised within 90 days of accrual.',
        ],
        [
            'name'                    => 'Jury Duty / Civic Leave',
            'code'                    => 'JDL',
            'color'                   => '#3b82f6',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 3,
            'max_consecutive_days'    => 0,
            'description'             => 'Paid leave for employees summoned for jury service, court attendance as a witness, or mandatory civic obligations by government order. Court summons must be presented.',
        ],
        [
            'name'                    => 'Blood Donation Leave',
            'code'                    => 'BDL',
            'color'                   => '#f43f5e',
            'unit'                    => 'hours',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 1,
            'max_consecutive_days'    => 0,
            'description'             => 'Paid time off (typically 4 hours) granted to employees donating blood at an approved blood bank or hospital. Donation certificate required.',
        ],
        [
            'name'                    => 'Sabbatical Leave',
            'code'                    => 'SAB',
            'color'                   => '#8b5cf6',
            'unit'                    => 'days',
            'is_paid'                 => 0,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 60,
            'max_consecutive_days'    => 180,
            'description'             => 'Extended unpaid leave of up to 6 months for professional development, research, writing or personal projects. Available after 5+ years of service, subject to board/executive approval.',
        ],
        [
            'name'                    => 'Garden Leave',
            'code'                    => 'GDL',
            'color'                   => '#65a30d',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 0,
            'max_consecutive_days'    => 0,
            'description'             => 'Paid administrative leave during a notice period where the employee is asked not to attend the workplace but remains employed. Typically used for departing senior staff to protect confidential information.',
        ],
        [
            'name'                    => 'Quarantine / Isolation Leave',
            'code'                    => 'QRL',
            'color'                   => '#f97316',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 0,
            'max_consecutive_days'    => 14,
            'description'             => 'Paid leave for employees ordered into quarantine or isolation by a health authority (e.g. contact tracing, infectious disease). Health authority directive or medical letter required.',
        ],
        [
            'name'                    => 'Parental Leave (Shared / Secondary Carer)',
            'code'                    => 'PAR',
            'color'                   => '#0d9488',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 30,
            'max_consecutive_days'    => 30,
            'description'             => 'Shared parental leave allowing secondary caregivers (any gender) to share a portion of the primary caregiver\'s maternity entitlement. Subject to company policy and applicable law.',
        ],
        [
            'name'                    => 'Disability / Rehabilitation Leave',
            'code'                    => 'DRL',
            'color'                   => '#6366f1',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 0,
            'max_consecutive_days'    => 30,
            'description'             => 'Paid leave for employees undergoing treatment, therapy or rehabilitation following a workplace injury, disability or serious illness. Medical specialist report required.',
        ],
        [
            'name'                    => 'Volunteer / Community Service Leave',
            'code'                    => 'VOL',
            'color'                   => '#84cc16',
            'unit'                    => 'days',
            'is_paid'                 => 1,
            'gender_restriction'      => 'none',
            'requires_proof'          => 1,
            'allow_half_day'          => 1,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 0,
            'include_weekends'        => 0,
            'notice_days_required'    => 7,
            'max_consecutive_days'    => 3,
            'description'             => 'Paid leave for approved community service, charity work or national volunteer programmes. Activity confirmation letter required. Up to 3 days per year.',
        ],
        [
            'name'                    => 'Administrative Suspension',
            'code'                    => 'SUSP',
            'color'                   => '#475569',
            'unit'                    => 'days',
            'is_paid'                 => 0,
            'gender_restriction'      => 'none',
            'requires_proof'          => 0,
            'allow_half_day'          => 0,
            'carry_forward'           => 0,
            'max_carry_forward'       => 0,
            'carry_forward_expiry_days' => 0,
            'encashable'              => 0,
            'include_public_holidays' => 1,
            'include_weekends'        => 1,
            'notice_days_required'    => 0,
            'max_consecutive_days'    => 0,
            'description'             => 'Unpaid administrative leave issued by the employer pending investigation of a disciplinary matter. Governed by the company\'s disciplinary procedure and Employment Act provisions.',
        ],
    ];

    foreach ($leave_types as $lt) {
        $CI->db->insert($p . 'hr_leave_types', array_merge($lt, [
            'status'       => 'active',
            'created_by'   => null,
            'date_created' => $now,
        ]));
    }
}

