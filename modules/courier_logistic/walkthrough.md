# Courier Module Updates - Phase 2 Walkthrough

## What was completed
1. **International Tariffs Matrix Upload:**
   - 1144 rate rows have been successfully imported into the origin-based matrix for "Kenya".
   - The frontend API `get_countries` endpoint URL was corrected so the "Origin Country" selection works again in the admin settings wizard.
   - The Client Portal Quote Calculator now utilizes these backend rates securely.

2. **Courier Invoice Refinements:**
   - **Watermark Logo:** Updated to sit centered without rotation and cover approximately 75% of the invoice background as requested.
   - **Secondary Theme Color:** The issue with the second color defaulting back to the primary one upon saving has been fixed (adjusted the color parsing validation). The chosen secondary color now properly applies to table headers and totals sections.

3. **Client Portal Zone Select:**
   - Fixed the visibility mapping of the zone selector for Domestic routes in the Client Portal. The quote engine once again properly prompts the user to select a delivery zone before proceeding.

## Testing & Next Steps
- Verify the new Invoice watermark format by printing/viewing a courier invoice.
- Change the `Courier Invoice Theme Color 2` in the Courier Customization settings and save to confirm it retains its value.
- Head to the Client Portal / Get a Quote to test a domestic shipment and an origin-based international shipment from Kenya.
