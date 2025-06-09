# Explanation – Above the Fold Link Tracker

## 🧩 Problem Summary

Website owners often assume visitors see their most important links — but do they really? This plugin was built to answer that question: **which links are actually visible above the fold** when users load the homepage?

By tracking these links across real user sessions, site owners can optimize their layouts for visibility and conversion.

---

## ⚙️ Technical Design

The solution is a modular WordPress plugin that uses JavaScript, REST API, and custom database storage.

### ✅ Features Overview

- Injects a JavaScript tracker into the homepage
- Captures screen size and visible `<a>` links above the fold
- Sends data to a custom REST API endpoint
- Stores data in a custom DB table
- Displays records in a sortable, filterable, paginated admin UI
- Includes CSV export and bulk deletion
- Runs a WP-Cron task to delete entries older than 7 days

---

## 🧱 Architecture

### 1. JavaScript Tracker
The plugin injects a JavaScript snippet into the homepage that:
- Waits for the page to load
- Measures the viewport size
- Collects all visible `<a>` links above the fold
- Sends this data to the REST API endpoint
- Example:
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const links = Array.from(document.querySelectorAll('a')).filter(link => {
        const rect = link.getBoundingClientRect();
        return rect.top >= 0 && rect.bottom <= window.innerHeight;
    });
    const screenSize = { width: window.innerWidth, height: window.innerHeight };
    fetch('/wp-json/atflt/v1/track', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ screen: screenSize, links: links.map(link => link.href) })
    });
});
```

### 2. REST API Endpoint
The plugin registers a custom REST API endpoint `/wp-json/atflt/v1/track` that:
- Accepts POST requests with JSON payload
- Validates the request
- Sanitizes and stores the data in a custom database table
- Example:
```php
add_action('rest_api_init', function () {
    register_rest_route('atflt/v1', '/track', [
        'methods' => 'POST',
        'callback' => 'atflt_track_links',
        'permission_callback' => '__return_true',
    ]);
});
function atflt_track_links(WP_REST_Request $request) {
    $data = $request->get_json_params();
    $screen = sanitize_text_field($data['screen']);
    $links = array_map('esc_url_raw', $data['links']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'atflt_links';
    $wpdb->insert($table_name, [
        'screen' => json_encode($screen),
        'links' => json_encode($links),
        'timestamp' => current_time('mysql')
    ]);
    
    return new WP_REST_Response(['status' => 'success'], 200);
}
```
### 3. Database Table
A custom database table `wp_atflt_links` is created to store:
- `id`: Auto-incrementing primary key
- `screen`: JSON-encoded screen size
- `links`: JSON-encoded array of visible links
- `timestamp`: Date and time of the visit
- Example:
```sql
CREATE TABLE wp_atflt_links (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, 
    screen JSON NOT NULL,
    links JSON NOT NULL,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```
[]: # │   └── tracker.php                # Main plugin logic
[]: # ├── css/
[]: # │   └── admin.css                 # Admin styles
[]: # ├── js/
[]: # │   └── track-links.js            # JavaScript tracker
[]: # ├── languages/
[]: # │   └── atflt.pot                 # Translation template
[]: # ├── above-the-fold-link-tracker.php  # Main plugin file
[]: # └── README.md                     # Documentation

### 4. Admin Dashboard
The plugin adds an admin page under "Tools" that:
- Displays a sortable, filterable table of tracked records
- Allows bulk deletion of records
- Provides a button to delete all records
- Example:
```php
add_action('admin_menu', function() {
    add_management_page('Above the Fold Tracker', 'Above the Fold Tracker', 'manage_options', 'atflt-tracker', 'atflt_admin_page');
});
function atflt_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'atflt_links';
    $records = $wpdb->get_results("SELECT * FROM $table_name ORDER BY timestamp DESC");
    include plugin_dir_path(__FILE__) . 'admin/admin-page.php';
}
```
### 5. WP-Cron Cleanup Task
A WP-Cron task runs daily to delete records older than 7 days:
```php
add_action('atflt_daily_cleanup', 'atflt_cleanup_old_records');
function atflt_cleanup_old_records() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'atflt_links';
    $wpdb->query("DELETE FROM $table_name WHERE timestamp < NOW() - INTERVAL 7 DAY");
}
if (!wp_next_scheduled('atflt_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'atflt_daily_cleanup');
}
```

### 6. Manual Cleanup Trigger
A manual cleanup can be triggered by visiting a specific URL:
```php
add_action('init', function() {
    if (isset($_GET['force_atflt_cleanup'])) {
        atflt_cleanup_old_records();
        wp_redirect(admin_url('tools.php?page=atflt-tracker'));
        exit;
    }
});
```

### 7. File Structure
The plugin follows a modular structure:
```
above-the-fold-link-tracker/
├── js/
│   └── track-links.js             # JavaScript injected into homepage
├── includes/
│   ├── db.php                     # Database creation and cleanup
│   ├── rest-api.php               # API endpoint logic
│   ├── functions.php              # Utility/manual trigger logic
├── admin/
│   └── admin-page.php             # Admin dashboard UI
├── above-the-fold-link-tracker.php   # Plugin bootstrap
└── README.md                     # Documentation
```


---

## 💡 Technical Decisions & Justification

| Area | Decision | Justification |
|------|----------|----------------|
| Data Storage | Custom table (`wp_atflt_visits`) | Clean separation from WordPress core tables |
| Link capture | `getBoundingClientRect()` | Reliable way to detect if an element is visible in the current viewport |
| JS Injection | Only on homepage | Performance and scope: we only track the homepage |
| REST API | Custom endpoint `/atflt/v1/track` | Enables decoupled data collection via JS |
| Serialization | `maybe_serialize()` on links array | Simple approach for storing variable-length link data |
| Cleanup | WP-Cron + manual `?force_atflt_cleanup` | Ensures long-term performance, testability, and reliability |
| Admin UI | Native HTML + WordPress styles | Lightweight and follows WP UI patterns |
| UX | Pagination, sorting, filtering, bulk delete | Makes it usable at scale and for real analysis |

---

## 👨‍💻 How It Works (End to End)

1. **User loads homepage**  
   `track-links.js` captures screen size + all visible links, then posts it to the REST API.

2. **PHP endpoint receives data**  
   The data is sanitized and stored in `wp_atflt_visits` with a timestamp.

3. **Admin views data**  
   Under “Tools > Above the Fold Tracker”, the user can:
   - Filter by screen size or link keyword
   - Sort results by date or screen dimensions
   - Export current view as CSV
   - Bulk delete records

4. **Plugin maintains itself**  
   A scheduled WP-Cron runs daily to clean up old records, keeping performance high.

---

## 🔍 Limitations & Future Enhancements

- Link visibility is calculated only on load — dynamic content (via AJAX) is not yet handled.
- No deduplication of identical visits.
- Export is CSV-only and includes all matched rows — could include filters, presets.
- Potential future ideas:
  - Aggregate views per link
  - Graphs for top seen URLs
  - Dynamic detection with `MutationObserver`
  - Per-user or device-type breakdown

---

## 🏁 Conclusion

This plugin provides a solid foundation for link visibility analytics on WordPress homepages. It’s built with performance, scalability, and clarity in mind — and is ready for real-world usage and future iteration.
