CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) UNIQUE NOT NULL,
    `student_id` VARCHAR(50) DEFAULT NULL,
    `faculty` VARCHAR(100) DEFAULT 'Faculty of Computing',
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'student') DEFAULT 'student',
    `profile_pic` VARCHAR(255) DEFAULT 'default_avatar.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `badge_color` VARCHAR(30) DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `news` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` VARCHAR(500) DEFAULT NULL,
    `content` LONGTEXT NOT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `category_id` INT DEFAULT NULL,
    `author_id` INT DEFAULT NULL,
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'published',
    `is_breaking` TINYINT(1) DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `views` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `priority` ENUM('normal', 'important', 'urgent') DEFAULT 'normal',
    `target_audience` VARCHAR(100) DEFAULT 'All Students',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `article_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `news_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `comment` TEXT NOT NULL,
    `status` ENUM('approved', 'pending', 'rejected') DEFAULT 'approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bookmarks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `news_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_news_bookmark` (`user_id`, `news_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_category_sub` (`user_id`, `category_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `student_id`, `faculty`, `password`, `role`) VALUES
(1, 'NSBM NewsDesk Admin', 'admin@gmail.com', 'ADM-001', 'University Administration', '$2y$10$gdsJtQq8VHzpKq0X681UkuuzDhQgExyMWx66bRi.XFpE/ElVVH8EC', 'admin'),
(2, 'Kavindu Perera', 'kavindu@gmail.com', '26.1-IFSL-045', 'Faculty of Computing', '$2y$10$Ka1ALGtyQ49dfZ80Fdsw6eRsBzO2s2bNyQTUrVTQ7gAAqlcqKIxuu', 'student'),
(3, 'Sanduni Silva', 'sanduni@gmail.com', '26.1-BM-112', 'Faculty of Business', '$2y$10$Ka1ALGtyQ49dfZ80Fdsw6eRsBzO2s2bNyQTUrVTQ7gAAqlcqKIxuu', 'student');

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `badge_color`) VALUES
(1, 'Academic', 'academic', 'Lectures, curriculum, international partnerships, degree programs, and academic announcements', 'primary'),
(2, 'Computing & Tech', 'computing-tech', 'Software engineering, AI innovations, workshops, hackathons, and technology sessions', 'info'),
(3, 'Events & Festivals', 'events-festivals', 'Campus cultural festivals, conferences, convocations, exhibitions, and university events', 'warning'),
(4, 'Sports & Fitness', 'sports-fitness', 'Inter-faculty matches, cricket championships, sports club achievements, and tournaments', 'success'),
(5, 'Student Life & Clubs', 'student-life', 'Student societies, volunteer programs, leadership circles, and campus life experiences', 'secondary'),
(6, 'General Announcements', 'general-announcements', 'University-wide notices, library hours, examination schedules, and campus updates', 'dark');

INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `image`, `category_id`, `author_id`, `status`, `is_breaking`, `is_featured`, `views`, `created_at`) VALUES
(1,
 'NSBM Green University Celebrates 10 Years of Excellence and National Impact',
 'nsbm-green-university-celebrates-10-years-of-excellence-and-national-impact',
 'NSBM Green University celebrated its 10th anniversary on 26 October 2026 with a grand ceremony at the NSBM Auditorium in Homagama, marking a decade of achievements, growth and contributions to Sri Lankan higher education.',
 '<p>NSBM Green University celebrated its 10th anniversary on 26 October 2026 with a grand ceremony at the NSBM Auditorium in Homagama, marking a decade of achievements, growth and contributions to Sri Lankan higher education. Held at the invitation of Founder and Vice Chancellor Prof. E. A. Weerasinghe and the Board of Directors, the ceremony brought together distinguished local and international guests, including diplomatic representatives, government officials, industry partners, alumni and leaders from NSBM’s international university partnerships.</p>
<p>Among the distinguished guests were the Vice-Chancellors of the University of Plymouth, United Kingdom, and Southern Cross University, Australia; the Executive President of Zhejiang Shuren University, China; and Assoc. Prof. Ranjith Ihalanayake, Dean and Head of Programme at the Business School of Victoria University, Australia.</p>
<p>A key highlight of the celebration was the formal exchange of a Memorandum of Understanding (MoU) between NSBM Green University and Zhejiang Shuren University, China, marking another step towards strengthening international academic collaboration and expanding opportunities for global engagement.</p>
<p>The anniversary ceremony also featured the launch of several significant initiatives and publications commemorating NSBM’s first decade. These included a commemorative stamp, the Vision to Legacy coffee table magazine, the NSBM Economic Impact Report 2016–2026, and NSBM NEXT, the university’s strategic roadmap for the next decade.</p>
<p>The launches reflected both the achievements of NSBM’s first ten years and its vision for the future, with a continued focus on academic excellence, innovation, international collaboration and the advancement of higher education in Sri Lanka. As a fully self-financed university, NSBM has continued to expand its contribution to the nation through investments in education, infrastructure and international partnerships.</p>
<p>Its tenth anniversary marked an opportunity to reflect on this journey while reaffirming its ambition to take Sri Lankan higher education to the global stage. As NSBM enters its next decade, the university remains committed to creating greater opportunities for students, strengthening global connections and contributing to the future development of Sri Lanka.</p>',
 'nsbm_10_years_celebration.jpg',
 6, 1, 'published', 1, 1, 540, '2026-10-26 16:02:04'),

(2,
 'NSBM Hosts Dr. Hans Wijayasuriya for Sri Lanka’s Digital Future Session',
 'nsbm-hosts-dr-hans-wijayasuriya-for-sri-lankas-digital-future-session',
 'NSBM Green University welcomed Dr. Hans Wijayasuriya, Chief Advisor to the President on the Digital Economy and Chairman of GovTech and the ICT Agency of Sri Lanka, for an insightful session with its students and academic staff on the future of Sri Lanka’s digital economy.',
 '<p>NSBM Green University welcomed Dr. Hans Wijayasuriya, Chief Advisor to the President on the Digital Economy and Chairman of GovTech and the ICT Agency of Sri Lanka, for an insightful session with its students and academic staff on the future of Sri Lanka’s digital economy. The session explored the opportunities and challenges shaping the country’s digital transformation, highlighting the growing importance of digital literacy, technological adaptability and the development of skills and competencies required to thrive in an increasingly digital world.</p>
<p>The discussion provided students with valuable perspectives on emerging developments in the digital economy and the role of the next generation in supporting the country’s digital advancement. During his visit, Dr. Wijayasuriya also met with Senior Deputy Vice Chancellor Prof. Chaminda Rathnayake and the University’s senior management.</p>
<p>The meeting provided an opportunity to exchange perspectives on the evolving role of higher education in supporting national development and preparing future-ready graduates equipped to respond to the changing demands of the digital economy. The engagement reinforced the importance of strengthening connections between academia and the evolving digital landscape, ensuring that higher education remains responsive to emerging industry needs and technological developments.</p>
<p>Such interactions also create valuable opportunities for students and academics to gain insights from industry and national-level expertise. The visit reflects NSBM Green University’s continued commitment to enriching the student learning experience through meaningful engagement with distinguished professionals and fostering an academic environment that connects knowledge, innovation and the needs of a rapidly transforming economy.</p>',
 'nsbm_dr_hans_digital_future.jpg',
 2, 1, 'published', 1, 1, 420, '2026-10-29 14:56:44'),

(3,
 'NSBM Opens Rs. 720 Million Civil Engineering Laboratory Complex',
 'nsbm-opens-rs-720-million-civil-engineering-laboratory-complex',
 'NSBM Green University ceremonially opened its state-of-the-art Civil Engineering Laboratory Complex, representing a significant Rs. 720 million self-financed investment in engineering education, on 26 October 2026 as part of its 10th anniversary celebrations.',
 '<p>NSBM Green University ceremonially opened its state-of-the-art Civil Engineering Laboratory Complex, representing a significant Rs. 720 million self-financed investment in engineering education, on 26 October 2026 as part of its 10th anniversary celebrations. Located within the university’s Phase II development at Pitipana, Homagama, the new complex marks an important milestone in NSBM’s continued investment in modern academic infrastructure and practical engineering education.</p>
<p>Equipped with modern infrastructure, high-tech laboratories and advanced learning facilities, the complex is designed to strengthen students’ practical learning experiences and support the development of industry-relevant engineering skills. The complex was ceremonially declared open by the Attorney General of Sri Lanka, Hon. K. A. Parinda Ranasinghe Jr., PC, in the distinguished presence of NSBM Founder and Vice-Chancellor Prof. E. A. Weerasinghe; Prof. Richard Davies, Vice-Chancellor and CEO of the University of Plymouth, United Kingdom; Prof. Tyrone Carlin, Vice-Chancellor and President of Southern Cross University, Australia; and Prof. Li Lu, Vice-Chancellor and President of Zhejiang Shuren University, China.</p>
<p>The Rs. 720 million investment reflects NSBM’s commitment to providing students with an academic environment that connects theoretical knowledge with practical application. By strengthening its engineering facilities, the university aims to equip future engineers with the technical knowledge, practical experience and competencies required to meet the evolving demands of the profession.</p>
<p>The opening of the complex also forms part of NSBM’s broader campus expansion programme, which seeks to enhance its academic capabilities and create opportunities for future-focused education across multiple disciplines. As NSBM marks a decade of excellence and national impact, the new Civil Engineering Laboratory Complex stands as a testament to its continued investment in education, infrastructure and the next generation of engineering professionals.</p>
<p>Through initiatives such as this, NSBM continues to advance its vision of strengthening Sri Lankan higher education and preparing graduates to contribute to the country’s development and beyond.</p>',
 'nsbm_civil_engineering_complex.jpg',
 1, 1, 'published', 0, 1, 380, '2026-10-26 10:21:15'),

(4,
 'ICOBI 2026 Explores Advancing Human-Centric AI and Sustainable Business Innovation',
 'icobi-2026-explores-advancing-human-centric-ai-and-sustainable-business-innovation',
 'The 9th International Conference on Business Innovation (ICOBI 2026), organized by the Faculty of Business, NSBM Green University, was successfully held under the timely and thought-provoking theme, “Human-Centric AI and Sustainable Business Innovation for Future Economy.”The main conference brought together distinguished academics, research...',
 '<p>The 9th International Conference on Business Innovation (ICOBI 2026), organized by the Faculty of Business, NSBM Green University, was successfully held under the timely and thought-provoking theme, “Human-Centric AI and Sustainable Business Innovation for Future Economy.”The main conference brought together distinguished academics, researchers, industry professionals, and scholars to exchange knowledge, share innovative research, and explore emerging perspectives on the future of business, technology, and sustainable development.</p>
<p>The inauguration ceremony was graced by the presence of the Vice Chancellor of NSBM, Prof. E. A. Weerasinghe, the Deputy Vice Chancellor-Academic, Prof. Thilini De Silva, Head of Academic Development and Quality Assurance, Prof. Baratha Dodankotuwa, Dean of the Faculty of Business, Ms. Anne Pathiranager,  Deans of other faculties,  Heads of Departments, alongside academic staff members, esteemed academic scholars, researchers, and other distinguished invited guests.</p>
<p>A major highlight of the conference was the delivery of two insightful keynote speeches by distinguished speakers representing academia and industry: Associate Professor Jacquiline Tham – Management and Science University, Malaysia and Mr. Supun Weerasinghe – Group Chief Executive Officer, Non-Independent, Executive Director,  Dialog Axiata PLC.</p>
<p>Their valuable insights and perspectives enriched the conference discussions, highlighting the evolving role of innovation, technology, and leadership in shaping the future of business and advancing sustainable development in an increasingly dynamic global environment. ICOBI 2026 also provided a valuable platform for researchers and academics to present their scholarly work, exchange ideas, foster interdisciplinary collaboration, and strengthen academic partnerships.</p>
<p>The conference reaffirmed NSBM Green University&#8217;s commitment to advancing research excellence, promoting knowledge sharing, and creating meaningful connections between academia and industry. The conference featured several noteworthy milestones, including the official launch of the ICOBI 2026 Proceedings and the unveiling of the International Journal of Contemporary Business Research (IJCBR) Volume 05 Issue 01.</p>
<p>The successful conclusion of ICOBI 2026 reflects the collective dedication and commitment of the conference organizing committee, keynote speakers, presenters, reviewers, academic staff, and all those who contributed to making this international academic gathering a success.</p>',
 'nsbm_icobi_2026.jpg',
 3, 1, 'published', 0, 1, 310, '2026-10-16 14:37:45'),

(5,
 'NSBM Green University Achieves GREENSL® Platinum Building Certification',
 'nsbm-green-university-achieves-greensl-platinum-building-certification',
 'NSBM Green University has been awarded the GREENSL® Platinum Building Certification, the highest rating under the GREENSL® Rating System, by the Green Building Council of Sri Lanka (GBCSL), recognising the University’s commitment to environmental sustainability, resource efficiency and responsible building operations.',
 '<p>NSBM Green University has been awarded the GREENSL® Platinum Building Certification, the highest rating under the GREENSL® Rating System, by the Green Building Council of Sri Lanka (GBCSL), recognising the University’s commitment to environmental sustainability, resource efficiency and responsible building operations.</p>
<p>The certification was awarded under the GREENSL® Rating System for Existing Buildings (Operations and Maintenance), Version 2. 0 (Pilot Version), following the University’s fulfilment of the system’s assessment requirements. The Platinum Rating represents the highest level of recognition under the GREENSL® certification framework.</p>
<p>The certification, together with the accompanying licence and plaque, was formally presented to the Vice Chancellor of NSBM Green University at the Green Building Awards Ceremony held on 28 October 2026. Achieved as NSBM celebrates its tenth anniversary, this recognition marks a significant milestone in the University’s ongoing sustainability journey.</p>
<p>It reinforces NSBM’s commitment to integrating environmental responsibility and resource efficiency into its institutional operations while continuing to advance academic excellence and innovation. The achievement further reflects the University’s commitment to supporting a greener and more resilient built environment in Sri Lanka, strengthening its pursuit of sustainable development alongside its educational mission.</p>',
 'nsbm_greensl_platinum.jpg',
 6, 1, 'published', 0, 0, 260, '2026-10-29 12:09:09'),

(6,
 'NSBM Green University Welcomes Final Undergraduate Intake for 2026',
 'nsbm-green-university-welcomes-final-undergraduate-intake-for-2026',
 'NSBM Green University welcomed the university’s final undergraduate intake for 2026 at an inauguration ceremony held on 20th October at its lush green campus in Homagama.',
 '<p>NSBM Green University welcomed the university’s final undergraduate intake for 2026 at an inauguration ceremony held on 20th October at its lush green campus in Homagama. The occasion held special significance as NSBM prepares to celebrate its 10th anniversary on 26 October 2026, marking a decade of academic excellence, growth and impact.</p>
<p>The new students commenced their academic journeys across a range of undergraduate degree programmes offered by the Faculties of Business, Computing, Engineering and Science. The inauguration marked the beginning of their university journey, introducing them to the academic opportunities, facilities and student life at NSBM.</p>
<p>The ceremony, arranged in three sessions at NSBM Auditorium, featured a keynote address, informative presentations and orientation sessions designed to familiarise the new entrants with the University&#8217;s academic framework and the key aspects of university life. The ceremony was graced by two eminent keynote speakers: Professor Nalin de Silva, Senior Professor of Chemistry (Chair) at the University of Colombo, and Mr. Shamal Gunawardene, Chief Operating Officer of CEAT Kelani Holdings (Pvt) Ltd.</p>
<p>Their addresses offered valuable insights and perspectives from the fields of academia and industry, encouraging the new students as they embarked on their higher education journeys. The gracious presence of Professor E. A. Weerasinghe, Vice-Chancellor of NSBM Green University, and the senior management team added more significance to the occasion, alongside members of the University&#8217;s senior management, academic staff, parents and students in the audience.</p>
<p>The speeches encouraged students to make the most of their university experience by embracing academic opportunities, developing their skills and actively participating in activities that support their personal and professional growth. For this new intake, NSBM offers more than 50 undergraduate degree programmes, including its own degree qualifications and foreign degree programmes delivered in partnership with international universities, alongside four foundation programmes.</p>
<p>Through its academic offerings and industry-oriented learning environment, the University aims to equip students with the knowledge and skills needed to respond to the changing demands of the professional world.</p>',
 'nsbm_undergraduate_intake_2026.jpg',
 5, 1, 'published', 0, 0, 195, '2026-10-20 11:01:47'),

(7,
 'NSBM Volleyball Team Emerges Champions at UOC Vice Chancellor’s Challenge Trophy 2026',
 'nsbm-volleyball-team-emerges-champions-at-uoc-vice-chancellors-challenge-trophy-2026',
 'NSBM Volleyball Team Emerges Champions at UOC Vice Chancellor\'s Challenge Trophy 2026 A proud moment for NSBM Green University as our Men’s Volleyball Team emerged Champions at the Vice Chancellor’s Challenge Trophy Volleyball Championship 2026, organised by the University of Colombo, following a remarkable victory over the University of Col...',
 '<p>NSBM Volleyball Team Emerges Champions at UOC Vice Chancellor\'s Challenge Trophy 2026 A proud moment for NSBM Green University as our Men’s Volleyball Team emerged Champions at the Vice Chancellor’s Challenge Trophy Volleyball Championship 2026, organised by the University of Colombo, following a remarkable victory over the University of Colombo Old Boys Team in the final.</p>
<p>Adding to this achievement, our Women’s Volleyball Team secured Second Runners-Up, showcasing outstanding talent, determination and sportsmanship throughout the championship. Competing against teams representing the University of Colombo, University of Sri Jayewardenepura, University of Moratuwa, KDU, SLIIT and KIU, our players demonstrated exceptional teamwork, resilience and competitive spirit on the court.</p>
<p>Our players were also recognised for their exceptional individual performances, earning prestigious awards at the championship. Best Player – Men’s Team: Ishen ThilochanaBest Attacker: Dasun Navodya Congratulations to both teams, our outstanding players, coach and everyone who contributed to this remarkable achievement.</p>
<p>Your hard work and dedication continue to make NSBM proud! </p>',
 'nsbm_volleyball_champions.jpg',
 4, 1, 'published', 0, 0, 230, '2026-10-14 10:02:00'),

(8,
 'NSBM FIT Programme Introduces Computing Students to Generative and Agentic AI',
 'nsbm-fit-programme-introduces-computing-students-to-generative-and-agentic-ai',
 'Faculty of Computing at NSBM Green University launched the first session of the NSBM FIT (Future Innovation &amp; Technology) Programme on Friday, 9 October 2026, introducing Plymouth Batch 13 final-year Computing students to emerging technologies and industry perspectives.',
 '<p>Faculty of Computing at NSBM Green University launched the first session of the NSBM FIT (Future Innovation &amp; Technology) Programme on Friday, 9 October 2026, introducing Plymouth Batch 13 final-year Computing students to emerging technologies and industry perspectives. The session explored how Generative AI and Agentic AI are transforming the software engineering profession, focusing on emerging technological developments, practical applications, and future career opportunities.</p>
<p>Key areas covered included distinguishing genuine innovation from hype, understanding Generative AI and autonomous AI agents, AI-assisted software development, and evolving software engineering practices. The discussion also highlighted the importance of human oversight and decision-making, AI reliability, ethical considerations, responsible AI use, and the impact of AI on employment, professional skills, and the evolving role of software engineers.</p>
<p>Students also gained hands-on experience exploring AI-driven tools and concepts through practical activities. Their active participation in discussions and enthusiasm for learning reflected a strong interest in understanding and applying emerging AI technologies. The session featured Mr. Anjana Chandrapala, AI, Data &amp; Digital Transformation Leader and Head of Solutions at Mitra Innovation, and Mr. Amila Dantanarayana, Enterprise Quality Engineering &amp; Delivery Leader at Mitra AI, who shared industry insights into the changing technological landscape and the future of software engineering.</p>
<p>The initiative reflects the commitment of Ms. Pavithra Subhashini, Senior Lecturer and Head of the Industry Collaboration Unit, Faculty of Computing, and Mr. Diluka Wijesinghe, Course Director for Plymouth Computing Degree Programmes, to strengthening industry–academia collaboration and enriching students’ learning through meaningful industry engagement.</p>
<p>The session encouraged students to look beyond AI tools, embrace continuous learning, and prepare for the evolving responsibilities of software engineers in an increasingly AI-driven world. Through the NSBM FIT Programme, the Faculty of Computing continues to bridge academic learning with industry practice, equipping future computing professionals with the knowledge, skills, and perspectives needed to navigate emerging technologies and the changing demands of the industry.</p>',
 'nsbm_generative_agentic_ai.jpg',
 2, 1, 'published', 0, 0, 345, '2026-10-13 15:23:23'),

(9,
 'NSBM and SCU Sign MOU For Sri Lanka’s First Double Degree',
 'nsbm-and-scu-sign-mou-for-sri-lankas-first-double-degree',
 'NSBM Green University and Southern Cross University, Australia, have signed a Memorandum of Understanding (MoU) to introduce Sri Lanka’s first double degree combining a Bachelor of Business and a Bachelor of Information Technology, commencing in 2027.',
 '<p>NSBM Green University and Southern Cross University, Australia, have signed a Memorandum of Understanding (MoU) to introduce Sri Lanka’s first double degree combining a Bachelor of Business and a Bachelor of Information Technology, commencing in 2027. The agreement marks a significant milestone in the longstanding partnership between the two universities and was formalised as part of NSBM Green University’s 10th Anniversary Celebrations.</p>
<p>The MoU was signed by Professor Tyrone Carlin, Vice-Chancellor of Southern Cross University, and Professor E. A. Weerasinghe, Vice-Chancellor of NSBM Green University, reaffirming both institutions’ shared commitment to expanding access to international higher education and preparing graduates for an increasingly interconnected and technology-driven world.</p>
<p>The proposed double degree is designed to equip students with a complementary blend of business knowledge and information technology skills, addressing the growing demand for graduates who can navigate the intersection of business and digital innovation. By bringing together these two disciplines, the programme aims to provide students with a broader academic foundation and prepare them for diverse career opportunities in the evolving global employment landscape.</p>
<p>Beyond the introduction of the new degree, the agreement reflects the continued development of the partnership between NSBM Green University and Southern Cross University. It provides a foundation for further academic collaboration, research opportunities and global engagement, strengthening the international outlook of both institutions.</p>
<p>The introduction of this double degree represents another step in NSBM Green University’s commitment to broadening educational opportunities for Sri Lankan students through international partnerships and industry-relevant academic programmes. As the University marks a decade of progress in higher education, this collaboration signals its continued ambition to deliver globally aligned learning experiences that prepare students for the demands of the future.</p>',
 'nsbm_scu_double_degree.jpg',
 1, 1, 'published', 0, 0, 290, '2026-10-29 12:16:02'),

(10,
 'NSBM Hockey Teams Achieve Titles at KDU Leaders’ Trophy 2026',
 'nsbm-hockey-teams-achieve-titles-at-kdu-leaders-trophy-2026',
 'The Women’s and Men’s Hockey Teams of NSBM Green University recorded commendable performances at the KDU Leaders’ Trophy 2026 Inter-University Sports Championship, with the Women’s Team securing the first runner-up position and the Men’s Team finishing as second runner-up.',
 '<p>The Women’s and Men’s Hockey Teams of NSBM Green University recorded commendable performances at the KDU Leaders’ Trophy 2026 Inter-University Sports Championship, with the Women’s Team securing the first runner-up position and the Men’s Team finishing as second runner-up.</p>
<p>Both teams demonstrated determination, teamwork and commitment throughout the competition, earning their places among the tournament’s top finishers. Their performances reflect the dedication they have brought to their training and their efforts to represent NSBM in inter-university sports.</p>
<p>The teams were guided by their coach, Mr. Shanaka, whose support and guidance contributed to their preparation and performance during the championship. NSBM Green University congratulates both teams and their coach on these achievements and wishes them continued success in their future competitions.</p>',
 'nsbm_hockey_kdu_leaders.jpg',
 4, 1, 'published', 0, 0, 180, '2026-10-19 09:47:08');

INSERT INTO `announcements` (`id`, `title`, `content`, `priority`, `target_audience`, `created_at`) VALUES
(1, 'Final Year Project (FYP) Interim Progress Presentations - Semester 2', 'All Batch 26.1 undergraduates in the Faculty of Computing are requested to submit their progress document and slide decks via the LMS by Friday, 5:00 PM. Presentations will take place in Computing Lab 04.', 'urgent', 'Faculty of Computing', '2026-10-04 08:30:00'),
(2, 'Library Extended Opening Hours for Upcoming Mid-Semester Assessments', 'In view of the approaching mid-semester examinations, the Main Digital Library and Study Carrels will remain open from 7:00 AM until 10:00 PM on weekdays and until 7:00 PM on weekends.', 'important', 'All Students', '2026-10-03 09:00:00'),
(3, 'NSBM Career Fair 2026 - Employer Registrations and Student CV Submissions Open', 'More than 80 leading multinational and national corporate partners will participate in the annual Career Fair. Students are advised to upload their updated CVs to the Career Guidance portal.', 'normal', 'Final Year Students', '2026-10-01 11:15:00');

INSERT INTO `comments` (`id`, `news_id`, `user_id`, `comment`, `status`, `created_at`) VALUES
(1, 1, 2, 'The 10-year milestone and the new Rs. 720 million engineering complex is a proud moment for all of us at NSBM!', 'approved', '2026-10-26 18:40:00'),
(2, 2, 3, 'Dr. Hans Wijayasuriya gave incredible insights into the digital economy roadmap. Very inspiring session!', 'approved', '2026-10-29 16:10:00'),
(3, 4, 2, 'ICOBI 2026 presentations on AI and sustainability opened so many research avenues for computing undergraduates.', 'approved', '2026-10-16 14:30:00');

INSERT INTO `subscriptions` (`user_id`, `category_id`) VALUES
(2, 1),
(2, 2),
(3, 1),
(3, 3);

INSERT INTO `bookmarks` (`user_id`, `news_id`) VALUES
(2, 1),
(2, 2),
(3, 1),
(3, 4);

INSERT INTO `article_submissions` (`id`, `user_id`, `category_id`, `title`, `content`, `image`, `status`, `submitted_at`) VALUES
(1, 2, 2, 'STEMUP Club Organizes CodeSprint Hackathon for Freshers', 'The STEMUP club of NSBM is organizing an exciting 24-hour coding sprint aimed at introducing first-year students to collaborative problem solving and algorithmic thinking.', 'nsbm_generative_agentic_ai.jpg', 'approved', '2026-10-04 18:20:00');

CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `event_date` DATE NOT NULL,
    `start_time` TIME DEFAULT '09:00:00',
    `location` VARCHAR(255) DEFAULT 'NSBM Green University',
    `image` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `events` (`title`, `description`, `event_date`, `start_time`, `location`, `image`) VALUES
('International Conference on Business Innovation (ICBI) 2026', 'Human-Centric AI and Sustainable Business Innovation for Future Economy. Featuring global keynote speakers and academic research presentations.', '2026-10-25', '09:30:00', 'Green University Auditorium', 'nsbm_icobi_2026.jpg'),
('ICTAR 2026 - International Conference on Advances in Computing', 'Exploring intelligent computational systems, cybersecurity architectures, and big data engineering across faculty research groups.', '2026-11-15', '09:00:00', 'Faculty of Computing Complex', 'nsbm_dr_hans_digital_future.jpg'),
('AWS Cloud Horizon 2026 - Cloud Builder Summit', 'Interactive cloud labs, DevOps pipelines, serverless architectures, and live demonstrations hosted by the NSBM AWS Student Builder Group.', '2026-11-20', '10:00:00', 'Computing Lab 01 & 02', 'nsbm_generative_agentic_ai.jpg'),
('NSBM Annual Sports Gala & Awards Night 2026', 'Celebrating athletic achievements, faculty championship winners, and sports personality awards across 16 university disciplines.', '2026-12-05', '18:00:00', 'Student Recreation Center', 'nsbm_volleyball_champions.jpg');
