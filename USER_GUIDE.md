# AWS Document System — User Guide

Welcome to the **AWS Document System**, your all-in-one platform for creating and managing professional business documents. This guide walks you through every feature.

---

## 1. Getting Started

### Logging In
1. Visit **pawt-services.com**
2. Click **Login** (top-right corner)
3. Enter your **email** and **password**
4. Click **Log in**

### Registering a New Account
1. On the login page, click **Register**
2. Fill in your **Name**, **Email**, and **Password**
3. Click **Register** — you'll be redirected to the Dashboard

> **Note:** New accounts are assigned the "User" role. An admin can upgrade your role if needed.

---

## 2. Dashboard

After logging in, you'll see your **Dashboard** with:

- **Welcome Card** — Shows your document counts:
  - **Total** documents
  - **Drafts** (in progress)
  - **Finalized** (completed, awaiting payment)
  - **Paid** (fully settled)
- **Total Value** — Filter by All / Paid / Pending / Drafts to see amounts *(Admin only)*
- **Daily Verse** — An inspirational verse that refreshes daily

Click any stat number to jump directly to the filtered documents list.

---

## 3. Creating a Document

### Step-by-Step
1. Click **Create Document** (button on the Documents page or the `+` icon)
2. **Choose a Document Type:**
   - **Statement of Account (SOA)** — For billing/invoicing
   - **Purchase Order (PO)** — For ordering supplies
   - **Quotation** — For price estimates
   - **Delivery Receipt (DR)** — For confirming deliveries
3. **Fill in the Details:**
   - **Control Number** — Auto-generated (e.g., QT-2026-00001) or enter your own
   - **Recipient Name** — Client or company name *(required)*
   - **Recipient Email** — Optional
   - **Recipient Phone** — Optional
   - **Recipient Address** — Optional
   - **Document Date** — Defaults to today
   - **Discount** — Enter a discount amount if applicable
4. **Add Line Items:**
   - Click **Add Item**
   - Enter **Item Name**, **Description** (optional), **Quantity**, and **Unit Cost**
   - The **Total** is calculated automatically (Qty × Unit Cost)
   - Add as many items as needed
   - Click the ✕ button to remove an item
5. Click **Save Document**

Your document is saved as a **Draft**.

---

## 4. Viewing & Managing Documents

### Documents List
- Navigate to **Documents** from the sidebar menu
- Use the **search bar** to find documents by name, control number, or recipient
- Filter by **status** (All, Draft, Finalized, Paid) or **type** (SOA, PO, Quotation, DR)
- Documents are paginated — use the page controls at the bottom

### Document Detail Page
Click any document to view its full details:
- **Recipient information** (name, email, phone, address)
- **Line items table** with quantities, unit costs, and totals
- **Subtotal**, **Discount**, and **Total Amount**

### Available Actions
| Button | What it Does |
|--------|-------------|
| **View PDF** | Opens the PDF preview in a new browser tab |
| **Download PDF** | Downloads the PDF file to your device |
| **Edit** | Opens the document for editing |
| **Delete** | Deletes the document (with confirmation) |
| **Mark as Complete** | Changes status from Draft → Finalized *(Admin only)* |
| **Mark as Paid** | Changes status from Finalized → Paid *(Admin only)* |

---

## 5. Sharing Documents Publicly

You can share any document with clients via a secure link — no account needed for them to view it.

### Generating a Share Link
1. Open the document you want to share
2. Click **Share Publicly**
3. Choose an expiry period: **5, 10, 15, or 30 days**
4. Click **Generate Link**
5. The share link is now active — click **Copy Link** to copy it to your clipboard

### What Your Client Sees
- A clean, read-only view of the document
- A **confidentiality notice** banner
- A **countdown** showing how many days the link remains active
- A **Download PDF** button (if download is enabled)

### Revoking Access
- Click **Revoke Access** on the document page to immediately disable the share link

### Link Expiry
- After the chosen period, the link automatically expires
- Expired links show a friendly "Document Expired" page

---

## 6. PDF Documents

Each document type generates a professionally formatted PDF:

| Type | PDF Contains |
|------|-------------|
| **Statement of Account** | Billing details, itemized charges, totals |
| **Purchase Order** | Order items, quantities, pricing, supplier info |
| **Quotation** | Price estimates, itemized breakdown, validity |
| **Delivery Receipt** | Delivered items, quantities, receiver section |

All PDFs include:
- Your company logo and header
- Control number and date
- Recipient information
- Itemized table with totals
- Signature area

---

## 7. Admin Features

If you have **Admin** access, you get additional capabilities:

### User Management
1. Go to **Admin → Users** from the sidebar menu
2. View all registered users with their roles and status
3. **Change a user's role** — Click the role dropdown to switch between Admin and User
4. **Enable/Disable accounts** — Toggle a user's active status

### Status Management
- Only admins can **Mark as Complete** (Draft → Finalized)
- Only admins can **Mark as Paid** (Finalized → Paid)

### Dashboard Totals
- Admins see the **Total Value** section with filters for All, Paid, Pending, and Draft amounts

---

## 8. Profile Settings

1. Click your **name** in the top-right corner → **Profile**
2. You can:
   - **Update your name and email**
   - **Change your password**
   - **Delete your account** (permanent, requires password confirmation)

---

## 9. Quick Reference

### Document Statuses
| Status | Meaning | Color |
|--------|---------|-------|
| **Draft** | Work in progress, can be edited | Gray |
| **Finalized** | Complete, awaiting payment | Green |
| **Paid** | Fully settled | Emerald |

### Control Number Format
| Type | Format | Example |
|------|--------|---------|
| Statement of Account | `SOA-YYYY-XXXXX` | SOA-2026-00001 |
| Purchase Order | `PO-YYYY-XXXXX` | PO-2026-00003 |
| Quotation | `QT-YYYY-XXXXX` | QT-2026-00012 |
| Delivery Receipt | `DR-YYYY-XXXXX` | DR-2026-00005 |

### Keyboard Shortcuts
| Shortcut | Action |
|----------|--------|
| `Esc` | Close any open modal/popup |

---

## 10. Troubleshooting

| Issue | Solution |
|-------|----------|
| Can't log in | Check email/password. Contact admin to verify your account is active. |
| Can't edit a document | Only **Draft** documents can be edited. Finalized/Paid documents are locked. |
| PDF won't download | Try the **View PDF** button first. Ensure pop-ups are not blocked. |
| Share link not working | The link may have expired. Generate a new one from the document page. |
| Missing admin features | Your account may have the "User" role. Contact an admin for a role upgrade. |

---

**Need help?** Contact your system administrator.
