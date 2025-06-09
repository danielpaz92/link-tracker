# Above the Fold Link Tracker

Tracks which hyperlinks were visible **above the fold** when users visited the homepage of a WordPress site. Built as a plugin using modern WordPress standards.

## 🔍 Features

- Detects visible `<a>` links on the homepage when the page loads.
- Collects screen size and stores with timestamp.
- REST API endpoint receives and stores the data.
- WordPress admin screen displays the last 7 days of visits.
- Daily cleanup of old data via WP-Cron.
- Manual cleanup available for testing (`?force_atflt_cleanup`).

---

## 🚀 Installation

1. Download or clone this repository into your WordPress plugins directory:
    ```
    wp-content/plugins/above-the-fold-link-tracker/
    ```
2. Activate the plugin through the WordPress admin panel.
3. That’s it! The plugin starts tracking homepage visits automatically.

---

## 📊 How to View Data

Go to: ```WordPress Admin > Tools > Above the Fold Tracker```
You will see a table with the following columns:

- **Date**: The date of the visit.
- **Screen Size**: The screen size of the visitor.
- **Links**: The number of links visible above the fold.
- **Timestamp**: The exact time of the visit.
- **Actions**: Options to delete individual records.
- **Delete All**: A button to delete all records.

## 🛠️ Manual Cleanup (for testing)

You can manually trigger cleanup of records older than 7 days by visiting:
```https://yourdomain.com/?force_atflt_cleanup```

This will delete all records older than 7 days, useful for testing purposes.


(Admin access required.)

---

## 🧪 REST API Endpoint

- **URL**: `/wp-json/atflt/v1/track`
- **Method**: `POST`
- **Payload Example**:
  ```json
  {
    "screen": { "width": 1366, "height": 768 },
    "links": [
      "https://example.com/about",
      "https://example.com/contact"
    ]
  }
    ```

## 📂 File Structure

```above-the-fold-link-tracker/
├── js/
│   └── track-links.js             # JavaScript injected into homepage
├── includes/
│   ├── db.php                     # Database creation and cleanup
│   ├── rest-api.php               # API endpoint logic
│   ├── functions.php              # Utility/manual trigger logic
├── admin/
│   └── admin-page.php             # Admin dashboard UI
├── above-the-fold-link-tracker.php   # Plugin bootstrap
├── Explanation.md
└── README.md
```

🧠 Built With
Native WordPress APIs

PSR-compliant PHP

No external dependencies

📌 Minimum Requirements
PHP 7.3+

WordPress 6.0+

MySQL 5.6+ (or equivalent MariaDB)

🧑‍💻 Author
Daniel Paz
Website: https://danielpazwp.com.br
GitHub: @danielpaz92