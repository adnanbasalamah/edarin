# Product Guidelines

## Prose & Communication Style
- Use **Bahasa Indonesia** as the primary language for all UI text, labels, and messages since users are Indonesian-speaking distributors and admins.
- Use **English** for technical terms, code identifiers, and API endpoints.
- Keep text concise and action-oriented. Buttons and links should use imperative verbs (e.g., "Simpan", "Hapus", "Tambah Toko").
- Error messages should be specific, helpful, and non-technical: "Koneksi terputus. Silakan coba lagi" instead of "HTTP 500 error".

## Brand Voice
- **Trustworthy & Reliable:** The tone should inspire confidence — this is a professional distribution system.
- **Respectful & Helpful:** Address users with "Anda" rather than informal "kamu".
- **Clear & Direct:** Avoid jargon. Distributors in the field need to understand actions immediately.

## UX Principles
- **Mobile-first for Distributors:** All distributor-facing screens must be optimized for small screens, touch targets (minimum 44px), and potentially slow internet connections.
- **Desktop-rich for Admin:** Admin dashboards can leverage larger screens with data tables, charts, and sidebars.
- **Offline Resilience:** Since distributors work in the field with potentially unstable connections, inputs should be validated locally and submit via API when connectivity is available.
- **Progressive Disclosure:** Show only essential information first. Advanced filters and detailed reports can be revealed on demand.
- **Consistent Navigation:** Bottom tab navigation for mobile (distributor), left sidebar for desktop (admin).

## Visual Design Guidelines
- **Colors:** Follow the defined design system in `desain/DESIGN.md`. Primary blue (#00468C) for key actions and headers; green (#46B450) for success states.
- **Typography:** IBM Plex Sans for headings, Inter for body text, JetBrains Mono for technical data (tracking IDs, codes).
- **Spacing:** 4px baseline grid. 16px margins on mobile, 32px on desktop.
- **Elevation:** Flat design with subtle borders and minimal shadows (tonal layer approach).
- **Shapes:** Soft 4px border radius for inputs/buttons, 8px for cards/containers.

## Accessibility
- All touch targets must be at least 44x44dp on mobile.
- Color contrast ratios must meet WCAG AA standards (4.5:1 for normal text, 3:1 for large text).
- Form inputs must have visible labels, not just placeholders.
- Loading states must be indicated with spinners or skeleton screens, never blank screens.

## Data Display Conventions
- Currency values should be formatted in IDR (Rp) with thousand separators.
- Dates should use Indonesian format: DD/MM/YYYY.
- Tables should support horizontal scroll on mobile with frozen first column (store/product name).
- Numbers in tables should be right-aligned for easy comparison.

## Design System Reference
All detailed design tokens (colors, typography scales, spacing, elevation) are defined in [desain/DESIGN.md](../desain/DESIGN.md).