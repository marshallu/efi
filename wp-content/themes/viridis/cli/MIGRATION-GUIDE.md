# Statamic to WordPress Migration Guide

## Pages to Create

### 1. Home Page (/)

**Status:** Published
**SEO Title:** Edwards Foundation Inc.

**Blocks:**

1. **Page Header Block**
   - Type: Image
   - Background Image: hero-image-new.jpeg
   - Dark Overlay: Yes
   - Subheading: (empty)

2. **Basic Block**
   - Heading: Our Mission
   - Content:
     > The Edwards Foundation, Inc. was organized and is operated exclusively for the benefit of, to perform the functions of and to carry out the purposes of Cabell Huntington Hospital, Inc. and the Marshall University Board of Governors by carrying out exclusively charitable, educational and scientific activities.
   - Media Type: Image
   - Image: mission-research-1750098193.jpg
   - Content Side: Right
   - Background Color: White

3. **Iframe Embed Block**
   - Code:
     ```html
     <div class="contact-form"><iframe class="lg:w-full xl:w-full mx-80" src="https://www.cognitoforms.com/f/ZcwE-3aGqECGPg2cut24Lg/330" allow="payment" style="border:0; width:100%;" height="506"></iframe></div>
     <script src="https://www.cognitoforms.com/f/iframe.js"></script>
     ```

**WordPress Settings:** Set as Front Page (Settings > Reading > Static Page)

---

### 2. About Us Page (/about-us)

**Status:** Draft (unpublished)
**SEO Title:** About Us - Edwards Foundation Inc.

**Blocks:**

1. **Page Header Block**
   - Type: Image
   - Background Image: hero-image-new.jpeg
   - Dark Overlay: No
   - Subheading: (empty)

2. **Basic Block**
   - Heading: About Us
   - Content:
     > The Edwards Foundation, Inc. was established in 1991 by philanthropist Joan C. Edwards. Born in London in 1918, Joan Cavill Edwards was a jazz singer who moved to New Orleans at the age of four. Her musical career included performances on New Orleans radio station WWL, movie shorts and singing with orchestras across major cities like New York, Pittsburgh and Chicago. While performing at Pittsburgh's William Penn Hotel, she met James F. Edwards, owner and CEO of National Mattress Company (Namaco). The couple married in 1937 and settled in Huntington, West Virginia.
     >
     > Joan and James Edwards were dedicated supporters of Marshall University, contributing to scholarships, healthcare initiatives, and medical research. In 1991, Joan Edwards founded the Edwards Foundation, Inc. to ensure her lasting impact on health care initiatives in the Huntington community.
     >
     > Marshall University's School of Medicine was renamed the Joan C. Edwards School of Medicine in 2000 in recognition of Joan C. Edwards' significant philanthropic contributions to the university and its medical programs. In addition, the Edwards Foundation played a pivotal role in the creation of the Edwards Comprehensive Cancer Center (now Edwards Cancer Institute), a collaborative project with Cabell Huntington Hospital and the Marshall University Joan C. Edwards School of Medicine. Completed in December 2005, the 67,000 square-foot facility offers comprehensive cancer care services, including diagnostics, treatment and support, all under one roof.
     >
     > Joan C. Edwards passed away in May 2006 after a courageous battle with cancer. Her legacy of philanthropy continues to impact the Huntington community through the ongoing work of The Edwards Foundation, Inc.
   - Media Type: Image
   - Image: edwards-long-1750098169.jpeg
   - Content Side: Left
   - Background Color: White

---

### 3. Apply Now Page (/apply-now)

**Status:** Published
**SEO Title:** Apply Now - Edwards Foundation Inc.

**Blocks:**

1. **Page Header Block**
   - Type: Image
   - Background Image: hero-image-new.jpeg
   - Dark Overlay: No
   - Subheading: (empty)

2. **Iframe Embed Block**
   - Code:
     ```html
     <div class="mt-20"><iframe src="https://www.cognitoforms.com/f/ZcwE-3aGqECGPg2cut24Lg/331" allow="payment" style="border:0;width:85%; padding-left: 20px; padding-right: 20px; margin-bottom: 20px;" height="2980"></iframe></div>
     <script src="https://www.cognitoforms.com/f/iframe.js"></script>
     ```

---

### 4. What We Fund Page (/what-we-fund)

**Status:** Published
**SEO Title:** What We Fund - Edwards Foundation Inc.

**Blocks:**

1. **Page Header Block**
   - Type: Image
   - Background Image: hero-image-new.jpeg
   - Dark Overlay: No
   - Subheading: (empty)

2. **Iframe Embed Block** (Main content - contains full HTML)
   - Code: (Large HTML section with full What We Fund content - see entries.json)

3. **Iframe Embed Block** (Contact form)
   - Code:
     ```html
     <div class="contact-form"><iframe class="lg:w-full xl:w-full mx-80" src="https://www.cognitoforms.com/f/ZcwE-3aGqECGPg2cut24Lg/330" allow="payment" style="border:0;width:100%;" height="506"></iframe></div>
     <script src="https://www.cognitoforms.com/f/iframe.js"></script>
     ```

---

## Images to Upload

Upload these images to the WordPress Media Library:

1. `hero-image-new.jpeg` - Used as background for all page headers
2. `mission-research-1750098193.jpg` - Used in Home page Basic block
3. `edwards-long-1750098169.jpeg` - Used in About Us page Basic block

These should be copied from the Statamic assets folder:
`/Users/cmccomas/herd/edwardscc/storage/app/assets/`

---

## Running the Migration Script

If you prefer to use the WP-CLI migration script:

```bash
cd ~/herd/efi
wp eval-file web/app/themes/viridis/cli/migrate-statamic.php /Users/cmccomas/Documents/edwards/entries.json
```

Note: The script creates pages with ACF block markup, but images will need to be assigned manually after upload.

---

## Post-Migration Checklist

- [ ] Upload all images to Media Library
- [ ] Verify Home page is set as Front Page
- [ ] Update ACF blocks with correct image selections
- [ ] Review About Us page and publish when ready
- [ ] Test all Cognito form embeds
- [ ] Configure Yoast SEO settings
- [ ] Set up navigation menu
