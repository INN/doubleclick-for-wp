
## Workflow for Publishing Advertisements

### Terms
##### Specific to Advertisements
* __Order__ - Found under __Delivery__, an Order contains line items.
* __Line Item__ - Found under __Delivery__, Line Items are attached to Orders and contain multiple Creatives.
* __Creatives__ - Creatives are images or animated banners attached to __Line Items__. They are a fixed pixel measure (i.e. the Mobile Leaderboard ad unit size is 320x50). Multiple resolutions of a single advertisement might be needed for responsive sizing. Multiple Creatives may be attached to one Line Item to accomplish this.

##### Specific to Websites
* __Ad Units__ - Found under __Inventory__, Ad Units target multiple Creative sizes.
* __Placements__ - Found under __Inventory__, Placements may target Ad Units.

### 1. Create an Order (i.e. Terrific Taco Truck)

Orders should be used to organize ads by group or company.

### 2. Create a Line Item, Point to Ad Unit (i.e. December Taco Bonanza)

A Line Item is effectively the unit for an advertisement. Targeting on Line Items points them to specific Ad Units.

### 3. Attach Creative(s) to Line Items (i.e. TacoDecember_Mobile, TacoDecember_Desktop, TacoDecember_Desktop_Alt)

Attach Creatives to the Line Item. Creatives allow for upload of multiple pixel densities to allow for retina advertising (if your whole page is vector and clean, a blurry ad breaks this quality and reflects poorly on both you and advertiser).

### 4. Create an Ad Unit (i.e. Global_Header_Leaderboard or Article_Rail1_Box)

Ad units support all the potential creative sizes for the ad. For example, a Leaderboard has a 320x50 for mobile and 728x90 for desktop. Targeting on Line Items points them at specific Ad Units. Targeting on Placements includes Ad Units.

### 5. Create Placements (i.e. Global_Header_Banner or Article_Rail1)

Placements are unique locations on your website. As you create and edit placements, point them at appropriate ad units.


## Learn more about Targeting

See the [Targeting](Targeting.md) documentation to learn how to narrow the display logic for where ads are loaded and placed.
