# Rent Fetch 0.38.0

- Rebuilt the floor plan and unit editing screens as responsive, accessible tabbed interfaces that retain the existing fields while organizing their rendering, assets, lazy loading, and save handling into focused components.
- Added prominent identity controls for floor plans and units, including friendly searchable property and floor plan selectors that show record names alongside their IDs.
- Added confirmation dialogs and server-side safeguards before changing sync-critical sources, relationships, floor plan IDs, or unit IDs.
- Required the appropriate relationship IDs when manually publishing properties, floor plans, and units, with incomplete manual records kept as drafts and explained through an administrator notice; sync-created records remain unaffected.
- Condensed the floor plan information fields into full-width four-column numeric rows, with compact help tooltips and a responsive two-column layout on smaller screens.
- Improved editor performance by lazy-loading diagnostics and synced floor plan image previews.
- Kept the Diagnostics tab active while navigating between related properties, floor plans, and units, and streamlined property diagnostic actions within the hierarchy section.
