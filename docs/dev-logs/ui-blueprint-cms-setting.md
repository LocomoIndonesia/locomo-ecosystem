# UI Blueprint: Content Management & Settings
**Author:** The UI/UX Architect
**Date:** 2026-04-09
**Status:** Draft

## 1. Sidebar Synchronization
**File:** `resources/views/livewire/admin/sidebar.blade.php` (Existing)
**Action:** Inject new menu items immediately below the "Data Management" section.

### Menu Structure
*   **Manajemen Konten**
    *   **Icon:** `article` (Material Symbols)
    *   **Route/Link:** `/admin/content` (atau livewire action)
    *   **State:** Hover effects with scaling & transition duration 300ms.
*   **Pengaturan Sistem**
    *   **Icon:** `settings` (Material Symbols)
    *   **Route/Link:** `/admin/settings`
    *   **State:** Same hover characteristics.

---

## 2. Content CMS Page (`content-cms.html` / Blade)
**Purpose:** Manage dynamic content like Blog Posts, Announcements, and Static Pages.

### Layout Configuration
*   **Background:** Antigravity Glassmorphism (bg-surface-container-low).
*   **Main Container:** Max-width 1600px container, centered.
*   **Header:** Title "Content Management" with a "Create New" floating action button.

### Table View (Post/Page List)
*   **Style:** Borderless table within an `ethereal-shadow` container.
*   **Columns:** 
    *   Title
    *   Type (Post / Page) - Rendered as colored Badges (e.g. bg-primary/10)
    *   Status (Published / Draft) - Rendered as Badges (Emerald vs Slate)
    *   Date
    *   Actions (Edit, Delete - faint icons on hover)

### Editor Form (Modal / Slide-over)
*   **Form Structure:** Vertical layout.
*   **Input Fields:**
    *   Title (Input Text, massive font size, transparent background)
    *   Slug (Auto-generated from title, tiny text)
    *   Type (Select: Post/Page)
    *   Status (Select: Draft/Published)
*   **Rich Text Editor (TipTap Headless Integration):** 
    *   **Decision:** Menggunakan **TipTap Headless Core** dipadukan dengan Alpine.js untuk manipulasi DOM internal.
    *   **Toolbar Styling:**
        *   Container: `flex items-center gap-2 p-2 bg-white/50 backdrop-blur-md rounded-2xl mb-2 border border-white`.
        *   Buttons (Bold, Italic, Bullet List, dll): `p-2 rounded-xl text-slate-500 hover:bg-white hover:text-primary transition-all duration-300 soft-shadow`.
        *   Active State: Jika format aktif, tombol berubah menjadi `bg-primary text-white`.
    *   **Editor Area:** `prose max-w-none p-6 min-h-[400px] bg-surface-container-low rounded-3xl focus:outline-none focus:ring-2 focus:ring-primary/20`.

---

## 3. System Settings Page (`content-settings.html` / Blade)
**Purpose:** Manage global singleton configuration vectors (Hero Title, SEO Meta, SMTP).

### Layout Configuration
*   **Background:** Same as CMS, `bg-surface`.

### Form Architecture (Vertical Dynamic Layout)
*   The form will iterate over setting items from the database.
*   **Container:** `bg-white/80 backdrop-blur-xl rounded-3xl p-8 shadow-ethereal border border-white`.
*   **Setting Groups (Examples):**
    *   **General:**
        *   Platform Name (Input Text)
        *   Hero Subtitle (Textarea)
    *   **System/Mailing:**
        *   Enable SMTP (Toggle Switch - built with Alpine.js, smooth pill transition)
        *   SMTP Host (Input Text)

### Micro-interactions
*   **Save Button:** Positioned sticky at the bottom right. Pulsing glow effect when unsaved changes exist (`shadow-primary/40`).
*   **Toggle Switch:** Ensure the toggle dot translates smoothly instead of jumping instantly.
