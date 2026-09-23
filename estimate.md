# LILY INTERIORS — ESTIMATE, PROPOSAL & AGREEMENT MODULE ARCHITECTURE
**File:** `estimate.md`  
**Purpose:** Living technical design, material catalog, data inventory, and implementation plan for the **Estimate & Agreement Generator (এস্টিমেট, প্রপোজাল ও এগ্রিমেন্ট বিল্ডার)** module in Profixapp.  
**Created:** September 2026  
**Status:** In Consultation / Active Blueprint  

---

## ১. সারসংক্ষেপ ও লক্ষ্য (Executive Summary & Vision)

লিলি ইন্টেরিয়র্সের বাস্তব কার্যপ্রণালীতে যেকোনো কমার্শিয়াল বা রেসিডেন্সিয়াল প্রজেক্ট শুরুর আগে ক্লায়েন্টকে একটি অত্যন্ত প্রফেশনাল এস্টিমেট ও এগ্রিমেন্ট প্যাকেজ জমা দিতে হয়। এই প্যাকেজটি মূলত **দুটি অংশে** বিভক্ত:

1. **কভার পেজ: অফিশিয়াল প্রপোজাল ও এগ্রিমেন্ট লেটার (Proposal & Agreement Paper):**  
   লিলি ইন্টেরিয়র্সের অফিশিয়াল লেটারহেডে ক্লায়েন্টের নাম, ঠিকানা, প্রজেক্ট সাবজেক্ট, কাজের মূল ক্যাটাগরিভিত্তিক সামারি টেবিল, প্রজেক্ট সম্পন্ন করার সময়সীমা (যেমন: ২৫-৩০ কর্মদিবস), বিশেষ শর্তাবলী (Notes), ৩-ধাপের পেমেন্ট কিস্তির শর্ত (৬০% অগ্রিম, ৩০% রানিং কাজ, ১০% হ্যান্ডওভার) এবং ম্যানেজিং ডিরেক্টরের অফিসিয়াল সিল ও ক্লায়েন্টের স্বাক্ষর ব্লক।
2. **ভিতরের পাতা: বিস্তারিত আইটেমভিত্তিক এস্টিমেট ও BOQ (Detailed Itemized Sheet):**  
   ট্রেডভিত্তিক (কমার্শিয়াল) অথবা রুমভিত্তিক (রেসিডেন্সিয়াল) প্রতিটি কাজের পূর্ণাঙ্গ টেকনিক্যাল বিবরণ (বোর্ড, প্লাই, পেইন্ট, গ্লাস ও লাইটিং ব্র্যান্ড), মাপ/কোয়ান্টিটি, একক রেট, টাকার অংক এবং সাব-টোটাল।

### বাস্তব চ্যালেঞ্জ (Real-World Reality):
* **এস্টিমেট একদিনে হয় না:** সাইট ভিজিট, পরিমাপ নেওয়া, ম্যাটেরিয়ালের রেট যাচাই এবং ক্লায়েন্টের রিকয়ারমেন্ট পরিবর্তনের কারণে একটি এস্টিমেট তৈরি করতে সাধারণত **৪ থেকে ৫ দিন সময়** লাগে।
* **বারবার লম্বা বিবরণ টাইপ করা ক্লান্তিকর:** প্রতিটি আইটেমের লম্বা টেকনিক্যাল স্পেসিফিকেশন প্রতিবার নতুন করে টাইপ না করে **প্রি-সেভড টেমপ্লেট ও ক্যাটাগরি** থেকে কয়েক ক্লিকে ইনপুট করার সুবিধা থাকতে হবে।

---

## ২. কোন পেইজে কীভাবে কাজ করবে (Page-by-Page UI & Workflow Blueprint)

পুরো সিস্টেমটি ৪টি মূল পেইজ/স্ক্রিনে অত্যন্ত সহজ ও সুন্দরভাবে সাজানো হবে:

```
                          [সাইডবার: "Estimates"]
                                    │
    ┌───────────────────────────────┼───────────────────────────────┐
    ▼                               ▼                               ▼
[Page 1: এস্টিমেট তালিকা]     [Page 2: স্মার্ট বিল্ডার]      [Page 4: ক্যাটালগ ম্যানেজার]
  - ড্রাফট ও ফাইনাল লিস্ট       - ক্লায়েন্ট ও কভার ডেটা       - নিজস্ব টেমপ্লেট সংরক্ষণ
  - ৪-৫ দিন ধরে এডিটিং          - ক্যাটাগরিভিত্তিক টেমপ্লেট     - নতুন আইটেম ও রেট আপডেট
  - স্ট্যাটাস ট্র্যাকিং          - লাইভ এক্সেল স্টাইল টেবিল
                                - অটো-সেভ ড্রাফট
                                    │
                                    ▼
                         [Page 3: ভিউ ও প্রিন্ট প্রিভিউ]
                           ├── ট্যাব ১: অফিসিয়াল প্রপোজাল লেটার (কভার)
                           ├── ট্যাব ২: বিস্তারিত আইটেম শিট (BOQ)
                           └── অ্যাকশন: PDF / প্রিন্ট / ১-ক্লিক প্রজেক্ট কনভার্ট
```

---

### স্ক্রিন ১: এস্টিমেট তালিকা পেইজ (`/estimates`)
এখানে আপনার চলমান ও পূর্ববর্তী সকল এস্টিমেট সাজানো থাকবে:
* **স্ট্যাটাস ব্যাজ (Status Badges):**
  * 🟡 `Draft` (হলুদ: খসড়া / কাজ চলছে - ৪-৫ দিন ধরে যেকোনো সময় এডিট করা যাবে)
  * 🔵 `Sent to Client` (নীল: ক্লায়েন্টের কাছে জমা দেওয়া হয়েছে)
  * 🟢 `Approved` (সবুজ: ক্লায়েন্ট কর্তৃক অনুমোদিত ও স্বাক্ষরিত)
  * 🟣 `Converted` (পার্পল: প্রজেক্টে রূপান্তর করা হয়েছে)
* **টেবিল কলাম:**
  * এস্টিমেট নং (e.g. `EST-2026-001`)
  * ক্লায়েন্ট ও প্রজেক্টের নাম (e.g. `ASIABIZ Technology - Commercial Interior`)
  * প্রজেক্ট টাইপ (Commercial / Residential)
  * মোট বাজেট (Grand Total TK)
  * তৈরি ও সর্বশেষ আপডেটের তারিখ (Last Updated)
  * স্ট্যাটাস
* **অ্যাকশন বাটন:**
  * ✏️ **Edit / Continue** (যে ড্রাফটে কাজ চলছে সেটি ৪-৫ দিন পরও ওপেন করে আবার কাজ করা যাবে)
  * 👁️ **View / Print** (প্রিন্ট ভিউ দেখা)
  * 📋 **Duplicate / Copy** (আগের কোনো এস্টিমেট ক্লোন করে নতুন এস্টিমেট বানানো)
  * 🔄 **Convert to Project** (অনুমোদিত হলে প্রজেক্টে কনভার্ট)
* **শীর্ষ বাটন:** `+ Create New Estimate` (নতুন এস্টিমেট শুরু)

---

### স্ক্রিন ২: এস্টিমেট ক্রিয়েটর ও মাল্টি-ডে ড্রাফট বিল্ডার (`/estimates/create` এবং `/estimates/edit?id=X`)
এই পেইজে আপনি ৪-৫ দিন ধরে ধাপে ধাপে এস্টিমেট তৈরি করবেন:

#### ধাপ ১: ক্লায়েন্ট ও কভার পেজের তথ্য (Client & Cover Page Setup)
* **ক্লায়েন্ট ও কোম্পানির নাম:** e.g. `ASIABIZ Technology`
* **ডেজিগনেশন ও ব্যক্তি:** e.g. `Managing Director`
* **লোকেশন ও সাইট ঠিকানা:** e.g. `Suite-1307, Multiplan Center, Elephant Road, Dhaka`
* **বিষয় (Subject):** e.g. `Agreement for Commercial Space Interior works (1st floor)...`
* **কাজের সময়সীমা (Duration):** e.g. `25-30 working days`
* **পেমেন্ট কিস্তির শর্ত (Payment Schedule):**
  * ১ম কিস্তি: ৬০% (ওয়ার্ক অর্ডারের সাথে অগ্রিম)
  * ২য় কিস্তি: ৩০% (৮০% কাজ সমাপ্তিতে)
  * ৩য় কিস্তি: ১০% (কাজ সমাপ্তির ৭ দিনের মধ্যে)
  *(এই পার্সেন্টেজগুলো ডিফল্ট থাকবে, তবে আপনি চাইলে পরিবর্তন করতে পারবেন)*

#### ধাপ ২: ক্যাটাগরি ও টেমপ্লেট থেকে দ্রুত আইটেম যুক্তকরণ (Quick Template Picker)
স্ক্রিনের ওপরে বা পাশে সুন্দর ক্যাটাগরি ট্যাব থাকবে:
* 📁 **Ceiling Work** | 📁 **Wall & Floor** | 📁 **Furniture / Carpentry** | 📁 **Electrical & Lighting** | 📁 **Paint Work**
* অথবা রেসিডেন্সিয়ালের জন্য: 🏠 **Master Bed** | 🏠 **Child Bed** | 🏠 **Living** | 🏠 **Dining** | 🏠 **Kitchen**

**ম্যাজিক ড্রপডাউন / অটো-কমপ্লিট:**
* আপনি ক্যাটাগরি সিলেক্ট করলেই লিলি ইন্টেরিয়র্সের স্ট্যান্ডার্ড আইটেমগুলো ভেসে উঠবে।
* যেমন: ফার্নিচার ক্যাটাগরিতে ক্লিক করলে দেখতে পাবেন:
  * `Full Height Wardrobe (18mm Akij G.ply, 0.5mm Formica, T-bit)`
  * `Reception Table 40"H x 96"L`
  * `Workstation 6-Person (Glass divider)`
* **`+ Add` এ ক্লিক করতেই:** সম্পূর্ণ টেকনিক্যাল বিবরণ, ইউনিট (`S.ft`) এবং স্ট্যান্ডার্ড রেট (`2,150`) সরাসরি টেবিলে বসে যাবে।
* **আপনার কাজ শুধু:** সাইট থেকে যে মাপ পেয়েছেন সেই কোয়ান্টিটিটি (যেমন: `57`) বসানো—বাকি হিসাব কম্পিউটার নিজেই করে নিবে! আপনি চাইলে যেকোনো লাইনের বিবরণ বা রেট যেকোনো সময় কাস্টমাইজ করতে পারবেন।

#### ধাপ ৩: লাইভ এক্সেল-স্টাইল টেবিল (Dynamic Spreadsheet Grid)
* সেকশন অনুযায়ী আলাদা টেবিল ব্লক থাকবে (যেমন: Section A, Section B, Section C)।
* **কলামসমূহ:** `SL` | `Description of Work` | `Unit` | `Quantity` | `Unit Price` | `Amount (TK)` | `Action`
* প্রতিটি সেকশনের নিচে **সাব-টোটাল** লাইভ আপডেট হবে।
* পেইজের একদম নিচে **গ্র্যান্ড টোটাল** এবং বাংলায়/ইংরেজিতে **স্বয়ংক্রিয় কথায় রূপান্তর** (`In Word: Twenty Four Lac...`) দেখা যাবে।

#### ধাপ ৪: ৪-৫ দিন ধরে ধাপে ধাপে কাজ ও অটো-সেভ (Multi-day Autosave & Draft System)
* **সেভ বাটন:** `💾 Save as Draft` (খসড়া সেভ করুন) এবং `✅ Finalize Estimate` (চূড়ান্ত করুন)।
* **অটো-সেভ ফিচার:** প্রতি ৩০ সেকেন্ড পর পর অথবা আপনি কোনো সংখ্যা পরিবর্তন করে অন্য ঘরে গেলেই স্বয়ংক্রিয়ভাবে ব্যাকগ্রাউন্ডে সেভ হয়ে যাবে।
* আপনি আজ সিলিং ও পেইন্টের মাপ তুললেন—সেভ করে রাখলেন। কাল ফার্নিচারের মাপ তুললেন—লিস্ট থেকে এডিট করে ফার্নিচার অ্যাড করলেন। ৪-৫ দিন ধরে পরিমার্জন করে যখনই সন্তুষ্ট হবেন, তখনই "Finalize" করবেন!

---

### স্ক্রিন ৩: ভিউ ও প্রিন্ট প্রিভিউ পেইজ (`/estimates/view?id=X`)
এখানে আপনি ক্লায়েন্টকে দেওয়ার মতো দুটি চমৎকার রেডিমেড ফরম্যাট পাবেন:

#### ট্যাব ১: অফিশিয়াল প্রপোজাল ও এগ্রিমেন্ট পেপার (Proposal Letter Cover)
* হুবহু আপনার পাঠানো লেটারহেডের মতো:
  * ওপরে লিলি ইন্টেরিয়র্সের কালারফুল ব্যানার, লোগো, ঠিকানা, ফোন ও ইমেইল।
  * নিচে তারিখ, প্রাপকের ঠিকানা ও সাবজেক্ট।
  * **ক্যাটাগরি সামারি টেবিল:** মূল কাজের বিভাগগুলোর সাব-টোটাল (Paint Work, Wall & Floor, Furniture, Electric) এবং মোট টাকা।
  * **নোট ও শর্তাবলী:** ড্যামেজ পলিসি, ২৫-৩০ দিনের সময়সীমা, পেমেন্ট না পেলে কাজ বন্ধের শর্ত।
  * **পেমেন্ট শিডিউল:** ৬০% - ৩০% - ১০% এর বক্স।
  * **স্বাক্ষর ব্লক:** বামে Managing Director (Md. Mustafizur Rahman) এর অফিসিয়াল সিল ও সাইন, ডানে ক্লায়েন্টের সাইন।

#### ট্যাব ২: বিস্তারিত আইটেম শিট (Detailed BOQ Sheet)
* সেকশন A, B, C, D অনুযায়ী প্রতিটি আইটেমের মাপ, স্পেসিফিকেশন ও বিস্তারিত রেটের ২-৪ পৃষ্ঠার পূর্ণাঙ্গ শিট।
* নিচে সাব-টোটাল ও গ্র্যান্ড টোটাল কথায় সহ।
* Jr. Architect ও CEO-এর স্বাক্ষর ব্লক।

#### প্রিন্ট অপশনসমূহ (One-Click Actions):
* 🖨️ **Print Cover / Proposal Letter** (শুধুমাত্র কভার এগ্রিমেন্ট পেজ প্রিন্ট)
* 🖨️ **Print Detailed BOQ** (শুধুমাত্র বিস্তারিত শিট প্রিন্ট)
* 📑 **Print Complete Book** (কভার + বিস্তারিত শিট একসাথে ফুল সেট প্রিন্ট)
* 📥 **Download PDF** (ক্লায়েন্টকে হোয়াটসঅ্যাপ বা ইমেইলে পাঠানোর জন্য)
* 🔄 **Convert to Active Project** (ক্লায়েন্ট সাইন করলে ১-ক্লিকেই এটিকে Profixapp-এর মূল প্রজেক্টে রূপান্তর করা যাবে!)

---

### স্ক্রিন ৪: টেমপ্লেট ও ক্যাটালগ ম্যানেজার (`/estimates/catalog`)
ভবিষ্যতে লিলি ইন্টেরিয়র্সের কোনো নতুন কাজের ধরন আসলে বা কোনো ম্যাটেরিয়ালের দাম বাড়লে-কমলে:
* আপনি সহজেই নতুন আইটেম যুক্ত করতে পারবেন।
* পুরোনো আইটেমের বিবরণ ও স্ট্যান্ডার্ড রেট আপডেট করতে পারবেন।

---

## ৩. মাস্টার আইটেম ও রেট লাইব্রেরি (Master Library Catalog)

| ক্যাটাগরি | আইটেম নাম ও বিশদ বিবরণ (Standard Specifications) | ইউনিট | রেট (টাকা) |
| :--- | :--- | :---: | :---: |
| **সিলিং** | Plain Particle Ceiling (12mm Star/Super on BT Garjan frame with matt enamel) | S.ft | 420 - 520 |
| **সিলিং** | Drop Beam Ceiling (7'-0" height with plastic paint) | S.ft | 540 |
| **সিলিং** | Gypsum Ceiling (24"x24" board on Garjan frame) | S.ft | 80 |
| **সিলিং** | Duco Finish Particle Ceiling (12mm on Garjan frame) | S.ft | 600 |
| **ফার্নিচার** | Full Height Cabinet / Wardrobe (18mm Akij G.ply, 0.5mm Formica auto pasting, T-bit, 102"H) | S.ft | 2,150 |
| **ফার্নিচার** | Low Height Cabinet - LHC (18mm melamine / HPL, 30"H x 24"D) | S.ft | 1,700 |
| **ফার্নিচার** | Kitchen Over Head Cabinet - OHC (18mm Akij G.ply, HPL, aluminum profile handle) | S.ft | 2,250 |
| **ফার্নিচার** | Kitchen Middle Cabinet - MHC (3/4" Akij G.ply with HPL) | S.ft | 3,100 |
| **ফার্নিচার** | Kitchen Lower Cabinet - LHC (3/4" Marine ply, HPL, SS hardware) | S.ft | 2,850 |
| **ফার্নিচার** | Dinner Wagon (18mm Akij G.ply, mercury glass shutter, marble top, 96"H x 16"D) | S.ft | 2,450 |
| **ফার্নিচার** | TV Cabinet (18mm HPL on Akij G.ply, Garjan frame, charcoal finish) | S.ft | 1,350 |
| **ফার্নিচার** | Dressing Unit with Touch LED Backlit Mirror & Stool | S.ft | 1,350 - 1,450 |
| **ফার্নিচার** | Curtain Pelmet Box (18mm Akij G.ply on Garjan frame with Duco paint) | S.ft | 800 - 1,050 |
| **ফার্নিচার** | King Size Bed 5'-6"x7'-0" (Solid wood, fabric foaming, Eurasia mattress 10y warranty) | nos | 1,15,000 |
| **ফার্নিচার** | Modern Reception Table (18mm HPL, 40"H x 96"L x 18"D) | nos | 44,000 |
| **ফার্নিচার** | Meeting Table 8' x 3' (HPL board with metal legs) | nos | 30,500 |
| **ফার্নিচার** | 6-Person Workstation Table (Clear glass divider, frosted film, melamine) | per. | 8,000 |
| **ফার্নিচার** | 3-Person Workstation Table (HPL top, drawer unit) | per. | 12,500 |
| **ফার্নিচার** | Hanging Common Basin Set (Granite top, round LED mirror, marine ply duco) | nos | 42,300 |
| **ফার্নিচার** | CTG Segun/Chambol Solid Wood Folding Door (with frosted glass) | S.ft | 1,850 |
| **ওয়াল/ফ্লোর** | 10mm Frameless Tempered Glass Partition (SS handles, VVP closer & stopper) | S.ft | 290 |
| **ওয়াল/ফ্লোর** | 10mm Frameless Glass Door 3'x7' / 2'-6"x7' (VVP auto closer) | nos | 11,000 - 13,500 |
| **ওয়াল/ফ্লোর** | Wall Partition (12mm Garjan ply, Garjan wood frame, HPL pasting) | S.ft | 400 - 800 |
| **ওয়াল/ফ্লোর** | Wall Paneling / Sofa Back Decoration (18mm Akij G.ply with Duco paint) | S.ft | 1,050 - 1,100 |
| **ওয়াল/ফ্লোর** | PVC CNC Bit Cut Paneling (Washroom toilet side wall) | S.ft | 460 |
| **ওয়াল/ফ্লোর** | Imported Charcoal Louver Paneling | S.ft | 750 |
| **ওয়াল/ফ্লোর** | Imported PVC Floor Carpet (Lift / Kitchen) | S.ft | 155 - 165 |
| **ওয়াল/ফ্লোর** | Imported Floor Carpet (Rooms & common space) | S.ft | 107 |
| **ওয়াল/ফ্লোর** | 35mm Green Grass Carpet (Veranda) | S.ft | 130 |
| **পেইন্ট** | Berger Plastic Paint ECE (1 coat sealer + 2 coat putty + 2 coat ECE roller finish) | S.ft | 30 - 45 |
| **পেইন্ট** | Berger Luxury BEE Paint (1 coat sealer + 3 coat putty + 1 coat sealer + 2 coat BEE foam) | S.ft | 70 |
| **পেইন্ট** | Berger Enamel Paint for Window/Veranda Grill (Metal surface) | S.ft | 50 |
| **ইলেকট্রিক্যাল** | LED Office Hang Light 4FT 72w / 8FT 120w (Energy+ brand) | nos | 2,550 - 5,150 |
| **ইলেকট্রিক্যাল** | LED Panel Light 12w round / 2'x2' 48w conceal panel | nos | 650 - 2,800 |
| **ইলেকট্রিক্যাল** | LED Exclusive 8-Ring Chandelier / Dining Jhar | nos | 15,000 - 31,240 |
| **ইলেকট্রিক্যাল** | LED Spot & Track Light 12w (Wall surface / Track) | nos | 1,275 - 1,790 |
| **ইলেকট্রিক্যাল** | LED Heavy Duty 3-faces Strip Light | mtr. | 220 |
| **ইলেকট্রিক্যাল** | Profile Light with aluminum channel & diffuser | RFT | 255 |
| **ইলেকট্রিক্যাল** | BRB Cables BYA (1.5 rm / 2.5 rm / 4.0 rm) | coil | 4,800 - 11,923 |
| **ইলেকট্রিক্যাল** | MK Deluxe / ART-DNA Premium Sockets & Switches | nos | 365 - 464 |
| **ইলেকট্রিক্যাল** | Project Electrical Wiring & Fitting Labor Charge | Job | 42,000 |

---

## ৪. ডেটাবেজ স্কিমা (Database Structure)

```sql
-- ১. মাস্টার আইটেম ক্যাটালগ (Lily Interiors Templates)
CREATE TABLE IF NOT EXISTS `app_estimate_catalog` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(50) NOT NULL,
  `item_name` VARCHAR(150) NOT NULL,
  `specifications` TEXT NOT NULL,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'S.ft',
  `default_rate` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ২. এস্টিমেট ও প্রপোজাল মাস্টার টেবিল
CREATE TABLE IF NOT EXISTS `app_estimates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `estimate_no` VARCHAR(50) NOT NULL UNIQUE,     -- e.g. EST-2026-001
  `client_name` VARCHAR(150) NOT NULL,
  `client_designation` VARCHAR(100) DEFAULT NULL, -- e.g. Managing Director
  `client_company` VARCHAR(150) DEFAULT NULL,     -- e.g. ASIABIZ Technology
  `client_address` TEXT DEFAULT NULL,
  `client_phone` VARCHAR(50) DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `project_type` ENUM('commercial','residential') DEFAULT 'commercial',
  `group_by` ENUM('category','room') DEFAULT 'category',
  `working_days` VARCHAR(50) DEFAULT '25-30 working days',
  `advance_pct` DECIMAL(5,2) DEFAULT 60.00,      -- 1st Installment 60%
  `running_pct` DECIMAL(5,2) DEFAULT 30.00,      -- 2nd Installment 30%
  `final_pct` DECIMAL(5,2) DEFAULT 10.00,        -- 3rd Installment 10%
  `total_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(14,2) DEFAULT 0.00,
  `grand_total` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft','sent','approved','converted') DEFAULT 'draft',
  `converted_project_id` INT DEFAULT NULL,        -- FK to app_projects
  `notes` TEXT DEFAULT NULL,                     -- Standard terms & conditions
  `prepared_by` VARCHAR(100) DEFAULT 'Md. Rukonuzzaman',
  `approved_by` VARCHAR(100) DEFAULT 'Md. Mustafizur Rahman',
  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৩. এস্টিমেটের বিস্তারিত আইটেমস (BOQ Items)
CREATE TABLE IF NOT EXISTS `app_estimate_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `estimate_id` INT NOT NULL,
  `section_name` VARCHAR(100) NOT NULL,
  `section_order` INT NOT NULL DEFAULT 1,
  `sl_no` INT NOT NULL DEFAULT 1,
  `description` TEXT NOT NULL,
  `unit` VARCHAR(20) NOT NULL,                    -- S.ft, nos, per., LS, job, RFT, mtr., coil
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`estimate_id`) REFERENCES `app_estimates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## ৫. বাস্তবায়ন প্রস্তুতি (Implementation Readiness)

এই ব্লুপ্রিন্ট ও আর্কিটেকচার অনুযায়ী সম্পূর্ণ সিস্টেমটি তৈরি করার জন্য যা যা প্রয়োজন তা নির্ধারণ করা হয়েছে:
1. `api/estimates.php` (এস্টিমেট ড্রাফট সেভ, লোড, ডুপ্লিকেট ও প্রজেক্ট কনভার্ট ব্যাকএন্ড API)
2. `views/estimates.php` (লিস্ট ভিউ ও ড্রাফট ট্র্যাকিং)
3. `views/estimate-builder.php` (টেমপ্লেট-পিকার ও স্প্রেডশিট গ্রিড এডিটর)
4. `views/estimate-view.php` (কভার এগ্রিমেন্ট ও বিস্তারিত BOQ প্রিন্ট ইঞ্জিন)
5. `includes/sidebar.php` (সাইডবারে "Estimates" ট্যাব সংযোজন)
