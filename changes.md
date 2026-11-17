# Changes

## 0.39.12

* Added an enabled-by-default setting and editable label for the single floor plan Available Units button, preserving its visibility on existing sites after updates.
* Added a downward arrow to the button and clarified floor plan button settings to distinguish the unit-list jump from leasing, contact, and tour links.
* Clarified default availability-link and no-availability-link labels without replacing saved site labels.

## 0.39.11

* Fixed tour play icon sizing and alignment by using a fixed 16px icon size instead of the medium text-size token, so site typography changes do not resize the icon.

## 0.39.10

* Added frontend image alt text that prioritizes image-specific API descriptions and captions, with property, floor plan, or apartment names as fallbacks.
* Preserved image descriptions in unit galleries that use floor plan photos and in enlarged lightbox images and thumbnails.
* Used individual Rent Manager image descriptions and captions instead of image-category descriptions, and added Entrata caption fallbacks.
* Fixed text wrapping, spacing, and status-dot alignment in the Rent Fetch admin bar dropdown.
