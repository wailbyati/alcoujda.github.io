-- American Language Center — schema + seed content
-- A modern, city-agnostic rebuild modelled on the ALC Mohammedia look.
--
-- Values wrapped in double underscores (__LIKE_THIS__) are placeholders that
-- are filled in per branch when the three city projects are generated:
--   ALC Oujda, Oujda, 45 Blvd Mohammed VI, Oujda 60000, +212 536 503 594, oujda@acamorocco.org,
--   oujda@acamorocco.org, https://www.facebook.com/AmericanLanguageCenterOujda, https://www.instagram.com/alcoujda, .
--
-- Import once via phpMyAdmin or:
--   mysql -u USER -p DBNAME < schema.sql
-- Re-imports are idempotent (INSERT ... ON DUPLICATE KEY UPDATE).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------
-- Admin users (dashboard login)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(120) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default login: username "admin" / password "changeme123"
-- CHANGE THIS PASSWORD after your first login (Settings > Account).
INSERT INTO admin_users (username, password_hash, display_name) VALUES
('admin', '$2y$10$xI8fygJIdrcSiJMTgYyU9uQRgtFnper0tuhEby97OOM3JCWsArlTW', 'ALC Admin')
ON DUPLICATE KEY UPDATE username = username;

-- ---------------------------------------------------------------
-- Site-wide settings (header/footer contact info, socials)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(60) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'ALC Oujda'),
('city_name', 'Oujda'),
('site_tagline', 'Bringing Morocco and the United States closer together through language, cultural exchange and new opportunities.'),
('meta_description', 'At ALC Oujda we work as a non-profit organization to bring people from Morocco and the United States closer together through language, cultural exchange, and new opportunities.'),
('address', '45 Blvd Mohammed VI, Oujda 60000'),
('phone', '+212 536 503 594'),
('email', 'oujda@acamorocco.org'),
('careers_email', 'oujda@acamorocco.org'),
('facebook_url', 'https://www.facebook.com/AmericanLanguageCenterOujda'),
('instagram_url', 'https://www.instagram.com/alcoujda'),
('linkedin_url', ''),
('youtube_url', ''),
('whatsapp_number', ''),
('office_hours', 'Monday – Friday: 8:30 – 17:00'),
('exam_results_url', ''),
('footer_note', '© 2026 ALC Oujda. All rights reserved.'),
('hero_cta_1_label', 'Register now'),
('hero_cta_2_label', 'Our programs'),
('hero_cta_3_label', 'About us')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ---------------------------------------------------------------
-- Content blocks — one row per editable chunk of copy, grouped by page
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS content_blocks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_slug VARCHAR(40) NOT NULL,
  block_key VARCHAR(60) NOT NULL,
  label VARCHAR(160) NOT NULL,
  block_type ENUM('text','richtext','image','stat') NOT NULL DEFAULT 'text',
  content TEXT,
  image_path VARCHAR(255),
  sort_order INT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY page_block (page_slug, block_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- `text`     -> single line input
-- `richtext` -> textarea, blank line = new paragraph (or one item per line where noted)
-- `image`    -> file upload, stored relative to the site root
-- `stat`     -> "150+ classes / week" — number and label are split on the first space
--
-- Lists (hero slides, highlights, FAQs, partners, clubs, bullets…) are migrated
-- into `content_items` automatically on first read, so the dashboard can
-- add / remove / reorder them. The blocks below are the seed source.
INSERT INTO content_blocks (page_slug, block_key, label, block_type, content, image_path, sort_order) VALUES
-- HOME — hero slider
('home','hero_eyebrow','Hero — small label above headline','text','Welcome to',NULL,1),
('home','hero_heading','Hero — headline','text','ALC Oujda',NULL,2),
('home','hero_body','Hero — supporting paragraph','richtext','We are a non-profit language center bringing people from Morocco and the United States closer together through English, cultural exchange and new opportunities.',NULL,3),
('home','hero_image_1','Hero slide 1','image',NULL,'assets/img/hero-1.svg',4),
('home','hero_image_2','Hero slide 2','image',NULL,'assets/img/hero-2.svg',5),
('home','hero_image_3','Hero slide 3','image',NULL,'assets/img/hero-3.svg',6),

-- HOME — what we offer
('home','offers_eyebrow','Highlights — label above heading','text','Why choose us',NULL,7),
('home','offers_heading','Highlights — heading','text','What We Offer',NULL,8),
('home','offers_intro','Highlights — intro paragraph','richtext','Everything you need to learn confidently, at a pace that fits your life.',NULL,9),
('home','offer_1_title','Highlight 1 — title','text','Qualified, caring teachers',NULL,10),
('home','offer_1_body','Highlight 1 — text','text','Experienced instructors trained to teach English as a foreign language, supported by an academic team that invests in their growth.',NULL,11),
('home','offer_2_title','Highlight 2 — title','text','English for every age',NULL,12),
('home','offer_2_body','Highlight 2 — text','text','Dedicated programs for kids, teens and adults, from first words to advanced exam preparation — all on one friendly campus.',NULL,13),
('home','offer_3_title','Highlight 3 — title','text','Clubs beyond the classroom',NULL,14),
('home','offer_3_body','Highlight 3 — text','text','Community service, arts, debate and conversation clubs that turn language learning into real cultural exchange.',NULL,15),
('home','offer_4_title','Highlight 4 — title','text','Part of a national network',NULL,16),
('home','offer_4_body','Highlight 4 — text','text','We belong to the American Cultural Association, a network of twelve American Language Centers across Morocco.',NULL,17),

-- HOME — about
('home','about_eyebrow','About — label above heading','text','About us',NULL,18),
('home','about_heading','About — heading','text','Welcome to ALC Oujda',NULL,19),
('home','about_body','About — paragraph','richtext','We are a non-profit center offering high-quality English and cultural programs. Our classes prepare learners for school, university, work and life, in a welcoming environment open to the whole community.',NULL,20),
('home','about_image','About — photo','image',NULL,'assets/img/about.svg',21),
('home','about_button_label','About — button label','text','More about us',NULL,22),
('home','about_button_url','About — button link','text','/about.php',NULL,23),
('home','about_points','About — bullet points (one per line)','richtext','American English taught by qualified teachers\nSmall, level-based classes for kids, teens and adults\nCulture and community programs open to everyone',NULL,24),

-- HOME — programs intro
('home','programs_heading','Programs — heading','text','English for Every Age and Level',NULL,25),
('home','programs_intro','Programs — intro paragraph','richtext','Every learner is placed at the right level, so classes move at a comfortable and motivating pace.',NULL,26),

-- HOME — pedagogy / approach
('home','pedagogy_eyebrow','Approach — label','text','Our approach',NULL,27),
('home','pedagogy_heading','Approach — heading','text','Learning by Doing',NULL,28),
('home','pedagogy_body','Approach — paragraph','richtext','Our classrooms are active and communicative. Students speak, listen, read and write from the very first lesson, working in pairs and small groups on tasks that reflect real life.\n\nProgress is measured every session, and teachers give personal feedback so that every learner knows exactly what to work on next.',NULL,29),
('home','pedagogy_image','Approach — photo','image',NULL,'assets/img/pedagogy.svg',30),

-- HOME — numbers
('home','stat_classes','Stat — classes per week','stat','150+ classes / week',NULL,31),
('home','stat_students','Stat — students per year','stat','3,000+ students / year',NULL,32),
('home','stat_clubs','Stat — weekly clubs','stat','20+ weekly clubs',NULL,33),
('home','stats_body','Numbers — closing paragraph','richtext','With 150+ classes per week, thousands of students every year and more than 20 weekly clubs, learning at the ALC extends well beyond the classroom.',NULL,34),

-- HOME — FAQ
('home','faq_eyebrow','FAQ — label','text','Questions',NULL,35),
('home','faq_heading','FAQ — heading','text','Frequently Asked Questions',NULL,36),
('home','faq_1_question','FAQ 1 — question','text','How do I know which level to join?',NULL,37),
('home','faq_1_answer','FAQ 1 — answer','text','New students take a short placement test before classes start. It helps us put you in the group where you will learn best — no preparation is needed.',NULL,38),
('home','faq_2_question','FAQ 2 — question','text','How long does each session last?',NULL,39),
('home','faq_2_answer','FAQ 2 — answer','text','Most courses run for one trimester (about ten weeks) with a total of 30 contact hours. Summer courses are more intensive and shorter.',NULL,40),
('home','faq_3_question','FAQ 3 — question','text','What ages do you teach?',NULL,41),
('home','faq_3_answer','FAQ 3 — answer','text','We welcome young learners from around age five through to adults and professionals. Each age group has its own dedicated program and pacing.',NULL,42),
('home','faq_4_question','FAQ 4 — question','text','Do I need to buy a course book?',NULL,43),
('home','faq_4_answer','FAQ 4 — answer','text','A course manual is required for a complete program and is not included in the registration fee. Books are available during the first two weeks of each session.',NULL,44),
('home','faq_5_question','FAQ 5 — question','text','Are online classes available?',NULL,45),
('home','faq_5_answer','FAQ 5 — answer','text','Yes — selected programs are offered online as well as in person. Ask our team which levels are currently available online.',NULL,46),
('home','faq_6_question','FAQ 6 — question','text','How do I register?',NULL,47),
('home','faq_6_answer','FAQ 6 — answer','text','Complete the registration form on this site, or call into the center. Our team will confirm your level, schedule and payment details.',NULL,48),

-- HOME — partners
('home','partners_eyebrow','Partners — label','text','Our network',NULL,49),
('home','partners_heading','Partners — heading','text','Connecting with Excellence',NULL,50),
('home','partner_1_name','Partner 1 — name','text','American Cultural Association',NULL,51),
('home','partner_1_url','Partner 1 — link','text','https://aca.org.ma/',NULL,52),
('home','partner_2_name','Partner 2 — name','text','U.S. Embassy in Morocco',NULL,53),
('home','partner_2_url','Partner 2 — link','text','https://ma.usembassy.gov/',NULL,54),
('home','partner_3_name','Partner 3 — name','text','EducationUSA',NULL,55),
('home','partner_3_url','Partner 3 — link','text','https://educationusa.state.gov/',NULL,56),
('home','partner_4_name','Partner 4 — name','text','AMIDEAST Morocco',NULL,57),
('home','partner_4_url','Partner 4 — link','text','https://amideast.org/',NULL,58),
('home','partner_5_name','Partner 5 — name','text','Ministry of National Education',NULL,59),
('home','partner_5_url','Partner 5 — link','text','',NULL,60),
('home','partner_6_name','Partner 6 — name','text','Regional University Partners',NULL,61),
('home','partner_6_url','Partner 6 — link','text','',NULL,62),

-- HOME — call to action
('home','cta_image','CTA band — background photo','image',NULL,'assets/img/cta.svg',63),
('home','cta_eyebrow','CTA band — label','text','Ready to start?',NULL,64),
('home','cta_heading','CTA band — heading','text','Your English journey starts here',NULL,65),
('home','cta_body','CTA band — paragraph','richtext','Join thousands of learners who have built confidence and opened new opportunities with us.',NULL,66),
('home','cta_button_label','CTA band — button label','text','Register now',NULL,67),
('home','cta_button_url','CTA band — button link','text','/registration.php',NULL,68),

-- ABOUT
('about','page_title','Page title','text','About Us',NULL,1),
('about','page_caption','Page title — caption','text','Bringing Morocco and the United States closer together',NULL,2),
('about','mission','Mission statement','richtext','ALC Oujda is a non-profit organization that brings people from Morocco and the United States closer together through language, cultural exchange and new opportunities.\n\nWe are part of a national network of American Language Centers and work within the American Cultural Association, a Moroccan-based, non-profit organization funded by tuition from its centers.',NULL,3),
('about','mission_image','Mission — photo','image',NULL,'assets/img/about.svg',4),
('about','initiatives_intro','Initiatives — intro line','text','In addition to language instruction, our center and the American Cultural Association share initiatives that put community service and cultural exchange into practice.',NULL,5),
('about','initiative_1_title','Initiative 1 — title','text','Community Service',NULL,6),
('about','initiative_1_body','Initiative 1 — body','richtext','Our community service program engages active volunteers who meet weekly and contribute to local associations. It is designed to build leadership skills through civic activities that help those in need.',NULL,7),
('about','initiative_1_image','Initiative 1 — image','image',NULL,'assets/img/community.svg',8),
('about','initiative_2_title','Initiative 2 — title','text','The American Arts Center',NULL,9),
('about','initiative_2_body','Initiative 2 — body','richtext','The American Arts Center offers a wide range of arts and cultural programming to the community, free of charge, as part of the wider American Language Center family in Morocco.',NULL,10),
('about','initiative_2_image','Initiative 2 — image','image',NULL,'assets/img/pedagogy.svg',11),
('about','initiative_3_title','Initiative 3 — title','text','Study Abroad Guidance',NULL,12),
('about','initiative_3_body','Initiative 3 — body','richtext','Through partnerships with advising programs, we help high-school and university students prepare for study abroad with guidance, workshops and personal support.',NULL,13),
('about','initiative_3_image','Initiative 3 — image','image',NULL,'assets/img/careers.svg',14),
('about','history','History of the ALC network','richtext','The first American Language Centers were established in Morocco in the 1950s to teach English as a foreign language.\n\nIn the 1970s the American Cultural Association was created to provide independent, non-profit management of the centers, keeping the same public mission.\n\nToday the ACA network spans twelve cities across Morocco, funded entirely by tuition, and continues to open new opportunities through language and cultural exchange.',NULL,15),
('about','network_intro','Network — intro line','text','The ALC network spans Morocco, from Tangier in the north to Agadir in the south.',NULL,16),

-- ACADEMICS / PROGRAMS
('academics','page_title','Page title','text','Programs',NULL,1),
('academics','page_caption','Page title — caption','text','English and French courses for every age and level',NULL,2),
('academics','programs_heading','Programs — heading','text','English for Every Age and Level',NULL,3),
('academics','programs_intro','Programs — intro paragraph','richtext','Not sure where to start? Tell us your goals and we will recommend the right program and level.',NULL,4),
('academics','overview_en','Overview (English)','richtext','Lesson times vary by level and availability. Contact details must be kept up to date when there is a change.\n\nThe course manual is not included in the registration fee and is required for a complete English program. No books are sold after the second week of each session.',NULL,5),
('academics','overview_fr','Overview (Français)','richtext','Les horaires des cours varient selon le niveau et la disponibilité. Les coordonnées doivent être mises à jour en cas de changement.\n\nLe manuel de cours n''est pas inclus dans les frais d''inscription et il est indispensable pour un programme complet d''anglais. Aucun livre n''est vendu après la deuxième semaine de chaque session.',NULL,6),
('academics','table_col_program','Tuition table — column 1','text','Program (EN)',NULL,7),
('academics','table_col_program_fr','Tuition table — column 2','text','Programme (FR)',NULL,8),
('academics','table_col_hours','Tuition table — column 3','text','Hours',NULL,9),
('academics','table_col_price','Tuition table — column 4','text','Price',NULL,10),
('academics','fee_notes','Fee notes (one per line)','richtext','Application fee (first registration, non-refundable): 100,00 DH\nA course manual is required and is not included in the tuition\nPrices are indicative — confirm current fees with the registration office',NULL,11),

-- CLUBS / COMMUNITY
('community','page_title','Page title','text','Clubs & Community',NULL,1),
('community','page_caption','Page title — caption','text','Clubs, activities and cultural exchange',NULL,2),
('community','overview','Activities & events overview','richtext','Learning at the ALC extends well beyond the classroom. We offer more than 20 weekly clubs, plus activities and events that bring students, teachers and the wider community together.\n\nAs a member of the American Cultural Association, we sponsor performances, exhibits and clubs in support of the arts, sciences, culture, education and the environment.',NULL,3),
('community','overview_image','Overview — photo','image',NULL,'assets/img/community.svg',4),
('community','clubs_heading','Clubs — heading','text','Clubs & Student Life',NULL,5),
('community','clubs_intro','Clubs — intro paragraph','richtext','Clubs are free for enrolled students and a great way to practise English in a relaxed setting.',NULL,6),
('community','club_1_title','Club 1 — title','text','Community Service Club',NULL,7),
('community','club_1_body','Club 1 — text','text','Weekly meetings and volunteering with local associations, building leadership through service.',NULL,8),
('community','club_2_title','Club 2 — title','text','Debate & Public Speaking',NULL,9),
('community','club_2_body','Club 2 — text','text','Practise argument, confidence and clear expression in a friendly, supportive group.',NULL,10),
('community','club_3_title','Club 3 — title','text','Arts & Culture Club',NULL,11),
('community','club_3_body','Club 3 — text','text','Explore painting, music, theatre and exchange between Moroccan and American cultures.',NULL,12),
('community','club_4_title','Club 4 — title','text','Reading & Conversation',NULL,13),
('community','club_4_body','Club 4 — text','text','Relaxed conversation based on short stories, articles and films, guided by a teacher.',NULL,14),
('community','csc_title','Community Service Club — title','text','Community Service Club',NULL,15),
('community','csc_body','Community Service Club — body','richtext','The Community Service Club meets weekly and its volunteers work with associations in the area, delivering tutoring, support services and friendship to people in need.\n\nMembership is open to ALC students and to anyone in the wider community who wants to take part.',NULL,16),
('community','csc_points','Community Service Club — bullet points (one per line)','richtext','Weekly meetings, with room for new volunteers at any time\nCivic activities that build practical leadership skills\nLong-term partnerships with local associations',NULL,17),
('community','csc_image','Community Service Club — image','image',NULL,'assets/img/community.svg',18),

-- CAREERS
('careers','page_title','Page title','text','Careers',NULL,1),
('careers','page_caption','Page title — caption','text','Teach, live and grow with us',NULL,2),
('careers','overview','Overview','richtext','We employ a team of English language teachers and support staff. Teachers are certified to teach English as a Foreign Language through internationally recognized courses.\n\nWhen recruiting, preference is given to courses of at least 120 contact hours that include a face-to-face teaching component and reflective feedback.\n\nOur academic year begins each September. Applications are accepted year round, though recruitment mainly takes place in spring for the upcoming year.',NULL,3),
('careers','how_to_apply','How to apply — intro','richtext','Foreign candidates should note that they will need to arrive with a criminal history background report certified no more than 60 days before arrival, plus original certificates and diplomas.',NULL,4),
('careers','how_to_apply_points','How to apply — bullet points (one per line)','richtext','A resume in PDF format\nA scanned copy of your passport\nScanned diploma(s) and your TESOL/TEFL certificate\nTwo or three reference letters in PDF\nA cover letter in PDF explaining your interest in teaching at the ALC and living in Morocco',NULL,5),
('careers','how_to_apply_image','How to apply — photo','image',NULL,'assets/img/careers.svg',6),
('careers','living_city_title','Living in the city — title','text','Oujda',NULL,7),
('careers','living_city','Living in the city — text','richtext','Oujda is a lively Moroccan city with a strong cultural heritage, welcoming cafés and easy access to the rest of the country. Teachers enjoy a comfortable quality of life, a low cost of living and a warm local community.\n\nFrom historic neighbourhoods and markets to nearby coastlines and mountains, there is always something new to explore between lessons.',NULL,8),
('careers','living_image_1','Living in the city — photo 1','image',NULL,'assets/img/about.svg',9),
('careers','living_image_2','Living in the city — photo 2','image',NULL,'assets/img/pedagogy.svg',10),
('careers','living_image_3','Living in the city — photo 3','image',NULL,'assets/img/community.svg',11),
('careers','living_image_4','Living in the city — photo 4','image',NULL,'assets/img/careers.svg',12),

-- CONTACT
('contact','page_title','Page title','text','Contact Us',NULL,1),
('contact','page_caption','Page title — caption','text','We would love to hear from you',NULL,2),
('contact','intro','Form — intro line','text','Tell us how we can help and we will get back to you shortly.',NULL,3),
('contact','map_image','Contact — photo','image',NULL,'assets/img/contact.svg',4),

-- REGISTRATION
('registration','page_title','Page title','text','Registration',NULL,1),
('registration','page_caption','Page title — caption','text','Applications are now open',NULL,2),
('registration','intro','Intro paragraph','richtext','We welcome learners of all ages and levels. Our flexible English programs are supported by an active campus community — with classes for every age, activities and clubs beyond the classroom.\n\nFill in the form below and our team will contact you with placement and enrollment details.',NULL,3),
('registration','before_you_register','Before you register (one per line)','richtext','Choose a program, or tell us "Not sure yet" and we will help you place\nA course manual is required and is not included in the registration fee\nBooks are available during the first two weeks of each session only\nAn application fee applies to a first, non-refundable registration',NULL,4)
ON DUPLICATE KEY UPDATE
  label = VALUES(label),
  block_type = VALUES(block_type),
  content = VALUES(content),
  image_path = VALUES(image_path),
  sort_order = VALUES(sort_order);

-- ---------------------------------------------------------------
-- Content items — repeatable, add/delete/reorderable list entries
-- (hero slides, highlights, FAQs, partners, clubs, bullets, stats…).
-- Rows are NOT seeded here: on first read the app migrates the
-- equivalent content_blocks entries into this table automatically
-- (see includes/functions.php), so re-imports never duplicate rows.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS content_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_slug VARCHAR(40) NOT NULL,
  section_key VARCHAR(60) NOT NULL,
  title VARCHAR(255) NOT NULL DEFAULT '',
  content TEXT,
  image_path VARCHAR(255),
  link VARCHAR(255),
  sort_order INT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY page_section (page_slug, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Language programs (Programs page)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  audience VARCHAR(120) NOT NULL DEFAULT '',
  level VARCHAR(120) NOT NULL DEFAULT '',
  description TEXT,
  duration VARCHAR(120) DEFAULT '',
  schedule VARCHAR(200) DEFAULT '',
  status ENUM('active','hidden') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO programs (title, audience, level, description, duration, schedule, status, sort_order) VALUES
('Kids English', 'Kids (5–11)', 'Beginner–Intermediate', 'Playful, story-based English to build confidence and curiosity.', '3 months per session', 'Weekday mornings & afternoons', 'active', 1),
('Teens English', 'Teens (12–17)', 'Beginner–Advanced', 'Communicative classes aligned with school programs and international exams.', '3 months per session', 'Weekday afternoons & Saturdays', 'active', 2),
('Adult English', 'Adults (18+)', 'Beginner–Advanced', 'Practical English for travel, study, and career.', '3 months per session', 'Morning, afternoon, and evening options', 'active', 3),
('Business & Exam Prep', 'Professionals', 'Intermediate–Advanced', 'Business English, TOEFL, IELTS, and corporate training.', 'Flexible', 'Weekday evenings & Saturdays', 'active', 4)
ON DUPLICATE KEY UPDATE
  audience = VALUES(audience),
  level = VALUES(level),
  description = VALUES(description),
  duration = VALUES(duration),
  schedule = VALUES(schedule),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

-- ---------------------------------------------------------------
-- Tuition items (Programs page pricing table — bilingual)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tuition_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name_en VARCHAR(160) NOT NULL,
  name_fr VARCHAR(160) NOT NULL,
  hours_note VARCHAR(120) DEFAULT '',
  price_dh VARCHAR(40) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY name_en (name_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tuition_items (name_en, name_fr, hours_note, price_dh, sort_order) VALUES
('Trimester', 'Trimestre', '30 hours', '1.300,00 DH', 1),
('Trimester (Online)', 'Trimestre (En Ligne)', '30 hours', '1.500,00 DH', 2),
('Conversation (Beg 5+)', 'Conversation (Beg 5+)', '2 hours/week, 8 weeks', '1.300,00 DH', 3),
('TOEIC Preparation (Int 4+)', 'TOEIC Preparation (Int 4+)', '30 hours', '1.300,00 DH', 4),
('BAC Preparation (Beg 5+)', 'BAC Préparation (Beg 5+)', '60 hours', '1.300,00 DH', 5),
('Club Membership', 'Abonnement Club', '30 hours', '600,00 DH', 6),
('IELTS Preparation (Int 4+)', 'IELTS Préparation (Int 4+)', '90 hours', '1.600,00 DH', 7),
('TOEFL Preparation (ADV 1+)', 'TOEFL Préparation (ADV 1+)', '2 sessions only', '3.900,00 DH', 8),
('Business English (Inter 2+)', 'Business English (Inter 2+)', '30 hours', '1.200,00 DH', 9),
('Teaching Training', 'Formation à l''Enseignement', '30 hours', '3.900,00 DH', 10),
('English Elegance (Only for ladies)', 'Élégance en Anglais (Réservé aux Femmes)', '30 hours', '1.300,00 DH', 11),
('Mommy & Me (In-person) (3–4 years old)', 'Maman et moi (Présentiel) (3 - 4 ans)', '30 hours', '1.800,00 DH', 12),
('Children (in-person)', 'Enfants (en présentiel)', '30 hours', '3.600,00 DH', 13),
('Children (Online)', 'Enfants (En Ligne)', '30 hours', '3.900,00 DH', 14),
('Teens', 'Adolescents', '30 hours', '1.300,00 DH', 15),
('Young Learners (ages 5-6)', 'Jeunes Apprenants (5 - 6 ans)', '30 hours', '3.600,00 DH', 16)
ON DUPLICATE KEY UPDATE
  name_fr = VALUES(name_fr),
  hours_note = VALUES(hours_note),
  price_dh = VALUES(price_dh),
  sort_order = VALUES(sort_order);

-- ---------------------------------------------------------------
-- Career FAQs + benefits
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS career_faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY question (question)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO career_faqs (question, answer, sort_order) VALUES
('How many contact hours per week would I teach?', 'Typically 18–21 hours (negotiable).', 1),
('Will I teach young learners as well as adults?', 'Yes — our young learners programs are rapidly growing. If your experience is limited, we offer support.', 2),
('What days/hours will I work?', 'Most classes are weekday evenings and weekends. Teachers have a five-day work week and specify their preferred days and hours.', 3),
('What is the academic calendar?', 'Fall/winter/spring terms are 10 weeks, with a 7–14 day break between. Summer courses are intensive (two weeks) and optional for teachers who have completed their contractual obligations.', 4),
('What support is offered for new teachers?', 'New instructors undergo a thorough orientation and are mentored throughout their first term. Housing assistance is offered on arrival, including a complimentary homestay.', 5),
('How am I paid, and can I wire money home?', 'Salaries are paid monthly in Moroccan dirhams. Once your bank account and residency/working papers are obtained, you may transfer up to 100% of your documented salary to a foreign account in your name.', 6),
('What are the holiday benefits?', 'In addition to Moroccan national and religious holidays, the calendar provides breaks of one to two weeks between each term. The center closes for the month of August, and teachers who complete their commitments early may be eligible for an extended summer holiday.', 7)
ON DUPLICATE KEY UPDATE
  answer = VALUES(answer),
  sort_order = VALUES(sort_order);

CREATE TABLE IF NOT EXISTS career_benefits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  text VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY text (text(190))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO career_benefits (text, sort_order) VALUES
('Competitive salary, paid in 12 equal monthly payments', 1),
('Housing, settling-in, and annual travel allowances for foreign nationals', 2),
('Comprehensive medical and disability insurance', 3),
('Generous pension program with employer contributions', 4),
('Annual holiday pay (approximately one month''s salary)', 5),
('Option for extended summer holiday', 6),
('Time off between each term', 7),
('Free language lessons for foreign nationals', 8)
ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order);

-- ---------------------------------------------------------------
-- Career openings — current job postings
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS career_openings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  department VARCHAR(120) DEFAULT '',
  location VARCHAR(160) NOT NULL DEFAULT 'Oujda, Morocco',
  employment_type ENUM('full-time','part-time','contract') NOT NULL DEFAULT 'full-time',
  description TEXT,
  requirements TEXT,
  application_deadline DATE NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO career_openings (title, department, location, employment_type, description, requirements, application_deadline, status, sort_order) VALUES
('English Language Teacher', 'Academic', 'Oujda, Morocco', 'full-time',
 'Teach communicative English to adults, teens, and kids using the ALC curriculum.',
 'Native-level English proficiency. CELTA, TEFL, or equivalent preferred.', NULL, 'open', 1)
ON DUPLICATE KEY UPDATE
  department = VALUES(department),
  location = VALUES(location),
  employment_type = VALUES(employment_type),
  description = VALUES(description),
  requirements = VALUES(requirements),
  application_deadline = VALUES(application_deadline),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

-- ---------------------------------------------------------------
-- Network of ALC centers (About page)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS network_centers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  website_url VARCHAR(255) DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO network_centers (name, image_path, website_url, sort_order) VALUES
('Agadir', 'assets/img/city-agadir.jpg', 'https://alcagadir.org', 1),
('Casablanca', 'assets/img/city-casablanca.jpg', 'https://alccasablanca.com', 2),
('El Jadida', 'assets/img/city-el-jadida.jpg', '', 3),
('Fes', 'assets/img/city-fes.jpg', 'https://alcfes.org', 4),
('Kenitra', 'assets/img/city-kenitra.jpg', 'https://alckenitra.org', 5),
('Marrakesh', 'assets/img/city-marrakesh.jpg', 'https://alcmarrakesh.com', 6),
('Meknes', 'assets/img/city-meknes.jpg', 'https://alcmeknes.org', 7),
('Mohammedia', 'assets/img/city-mohammedia.jpg', 'https://alcmohammedia.com', 8),
('Oujda', 'assets/img/city-oujda.jpg', '', 9),
('Rabat', 'assets/img/city-rabat.jpg', 'https://alcrabat.org', 10),
('Tangier', 'assets/img/city-tangier.jpg', 'https://alctangier.org', 11),
('Tetouan', 'assets/img/city-tetouan.jpg', '', 12)
ON DUPLICATE KEY UPDATE
  image_path = VALUES(image_path),
  website_url = VALUES(website_url),
  sort_order = VALUES(sort_order);

-- ---------------------------------------------------------------
-- Posts / News
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  excerpt VARCHAR(400) DEFAULT '',
  body TEXT,
  image_path VARCHAR(255),
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO posts (title, slug, excerpt, body, image_path, status, published_at) VALUES
('New session now open for registration', 'new-session-registration',
 'Enrolment is open for learners of all ages and levels — reserve your place today.',
 'Enrolment for the new session is now open. Classes are available for kids, teens and adults, in person and online, at every level from beginner to advanced.',
 'assets/img/community.svg', 'published', '2026-09-01 00:00:00'),
('Summer clubs and cultural events', 'summer-clubs-events',
 'Our clubs and cultural activities continue throughout the summer.',
 'From community service to arts, debate and conversation, our weekly clubs keep students learning and connecting beyond the classroom all summer long.',
 'assets/img/pedagogy.svg', 'published', '2026-07-01 00:00:00')
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  excerpt = VALUES(excerpt),
  body = VALUES(body),
  image_path = VALUES(image_path),
  status = VALUES(status),
  published_at = VALUES(published_at);

-- ---------------------------------------------------------------
-- Contact + registration form submissions
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(60) DEFAULT '',
  program_interest VARCHAR(160) DEFAULT '',
  notes TEXT,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
