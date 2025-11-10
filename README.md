# PING - Project Subject Submission Site

Simple web prototype to create and manage PING (Projet Ingénieur) subjects. Built as an Esigelec student project (2025). This repository is an archive of the school deliverable: it contains the site pages, basic backend scripts and design documents - some files (SQL migrations, assets or config) may be missing. Comments inside the code are written in French.

---

## What this project does
- Let company tutors submit project subjects with: title, optional image, short summary, one or more PDF attachments, option for two teams, confidentiality flag.  
- Provide a review workflow for PING managers to validate, request changes or refuse a subject.  
- Support role-based access: Visitor, Tutor, PING Manager, Admin.  
- Deliverables follow the course requirements: site tree, mobile/desktop mockups, DB diagram and source code pages.

---

## Repository structure
- /css/  
  - style.css  
- /includes/  
  - header.inc.php, menu.inc.php, footer.inc.php, message.inc.php, param.inc.php  
- /pages/  
  - index.php, authentification.php, creationCompteTuteur.php, creationSujet.php, gestionDesRoles.php, gestionSujet.php, modifierSujet.php, sujetEnvoyes.php  
- /scripts/  
  - ttConnexion.php, ttInscription.php, ttCreationSujet.php, downloadPdf.php, deconnexion.php  
- /src/  
  - esigeleclogo_light.png, esigeleclogo_dark.png, default.jpg, VideoPromo2025.mp4
  
---

## High-level database model
(If SQL scripts are missing, recreate these tables using the code as reference.)

- users: id, username, email, password_hash, role, active, created_at  
- projects (subjects): id, title, summary, image_path, allow_two_teams (bool), confidentiality, status, tutor_id, created_at, updated_at  
- pdf_files (attachments): id, subject_id, filename, filepath, uploaded_at  
- roles / actions (optional): track reviews, comments, status changes

Status flow: draft/en attente → submitted → validated / modifications requested / refused.

---

## Quick run / setup (minimal)
1. Create a MySQL database and user for the app.  
2. If no SQL scripts exist, inspect the PHP scripts in /scripts/ and /pages/ to reconstruct the schema and required tables.  
3. Update DB connection settings in param.inc.php or the file that holds DB credentials. Never commit real credentials.  
4. Deploy files to a PHP-enabled web server (Apache or Nginx + PHP-FPM). Ensure upload directories are writable for attachments.  
5. Visit the site, create a tutor account, then activate it (via DB or role-management page) or create an admin/manager account directly in the DB to test validation flows.

Example checklist:
- Configure MySQL and create DB (e.g., ping_db).  
- Edit /includes/param.inc.php with DSN, DB user and password.  
- Import schema or create tables manually.  
- Place uploaded assets in /src/ or the configured uploads folder.  
- Open index.php in browser.

---

## Missing files / archive warning
This repo is an archived student deliverable. Some resources may be absent:
- SQL migration or creation scripts may be missing.  
- Some images, PDFs or video files referenced in pages may not be present.  
- Config files may contain placeholders or be absent.  
Be prepared to recreate simple SQL scripts and adjust paths or config before the site runs.

---

## Security and production notes
- Use prepared statements / parameterized queries to avoid SQL injection.  
- Hash passwords (bcrypt or similar) before storing.  
- Add CSRF protections and input sanitization for all forms.  
- Do not expose credentials in repository; use environment variables or a secure config method.

---

## Design & UX notes
- Mobile-first responsive design with Bootstrap 5 (accordions for subjects, hamburger menu on small screens).  
- UI pages include: home (presentation + list of validated non-confidential subjects), authentication, account creation for tutors, subject creation/editing, role management and subject review.
