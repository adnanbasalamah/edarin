---
name: Edarin Distribution System
colors:
  surface: '#f9f9fc'
  surface-dim: '#dadadc'
  surface-bright: '#f9f9fc'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3f6'
  surface-container: '#eeeef0'
  surface-container-high: '#e8e8ea'
  surface-container-highest: '#e2e2e5'
  on-surface: '#1a1c1e'
  on-surface-variant: '#424751'
  inverse-surface: '#2f3133'
  inverse-on-surface: '#f0f0f3'
  outline: '#737782'
  outline-variant: '#c2c6d2'
  surface-tint: '#2a5ea5'
  primary: '#003063'
  on-primary: '#ffffff'
  primary-container: '#00468c'
  on-primary-container: '#8cb6ff'
  inverse-primary: '#a9c7ff'
  secondary: '#006e1f'
  on-secondary: '#ffffff'
  secondary-container: '#89f88a'
  on-secondary-container: '#007321'
  tertiary: '#003731'
  on-tertiary: '#ffffff'
  tertiary-container: '#005048'
  on-tertiary-container: '#53c7b7'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d6e3ff'
  primary-fixed-dim: '#a9c7ff'
  on-primary-fixed: '#001b3d'
  on-primary-fixed-variant: '#00468c'
  secondary-fixed: '#8cfb8d'
  secondary-fixed-dim: '#70de74'
  on-secondary-fixed: '#002204'
  on-secondary-fixed-variant: '#005315'
  tertiary-fixed: '#85f6e5'
  tertiary-fixed-dim: '#67d9c9'
  on-tertiary-fixed: '#00201c'
  on-tertiary-fixed-variant: '#005048'
  background: '#f9f9fc'
  on-background: '#1a1c1e'
  surface-variant: '#e2e2e5'
typography:
  display-lg:
    fontFamily: IBM Plex Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: IBM Plex Sans
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: IBM Plex Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-md:
    fontFamily: IBM Plex Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-sm:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 32px
  max-width: 1440px
---

## Brand & Style
The design system is built for a high-performance logistics and distribution environment. It prioritizes reliability, efficiency, and clarity, reflecting a professional industrial standard. The visual style is **Corporate / Modern**, utilizing a structured grid and a high-contrast palette to ensure complex data is easily digestible. The brand personality is trustworthy and precise, evoking a sense of momentum and seamless operational flow, inspired by the circular, regenerative motion within the primary visual identity.

## Colors
The color palette is derived directly from the distribution cycle visual. 
- **Primary Blue (#00468C):** Used for core branding, primary actions, and structural headers to instill trust and authority.
- **Vibrant Green (#46B450):** Applied to success states, completed shipments, and positive growth indicators.
- **Teal Accent (#009688):** Utilized for secondary progress bars and interactive links to differentiate from primary blue actions.
- **Neutral Palette:** High-contrast slate and grey tones ensure legibility against white backgrounds, maintaining a clean, professional "utility-first" appearance.

## Typography
The typography system uses a tiered approach for high-density data environments. **IBM Plex Sans** provides a structured, technical feel for headlines and titles. **Inter** is the workhorse for body text, selected for its exceptional legibility in dashboards and tables. **JetBrains Mono** is reserved for metadata, tracking numbers, and technical IDs to ensure characters are distinct and easily readable at small sizes.

## Layout & Spacing
The system employs a **Fluid Grid** with a 12-column structure for desktop. 
- **Desktop:** 32px side margins with 24px gutters.
- **Tablet:** 8-column grid with 24px margins.
- **Mobile:** 4-column grid with 16px margins.

The spacing rhythm is based on a 4px baseline, ensuring all components align perfectly in data-heavy layouts. Content containers should favor a "top-down" information hierarchy with vertical stacks separated by 32px (8 units) to provide breathing room between distinct data modules.

## Elevation & Depth
This design system uses **Tonal Layers** and **Low-Contrast Outlines** rather than heavy shadows to maintain a clean, professional aesthetic. 
- **Surface Level 0:** The main background (Pure White #FFFFFF).
- **Surface Level 1:** Secondary containers or sidebars (Light Grey #F8F9FA) with a 1px border (#E9ECEF).
- **Surface Level 2:** Floating cards or modals, utilizing a very soft, diffused ambient shadow (0px 4px 20px rgba(0, 70, 140, 0.05)) to suggest subtle lift without cluttering the interface.

## Shapes
A **Soft** shape language is used to balance the technicality of the typography.
- Standard components (Inputs, Buttons) utilize a **0.25rem (4px)** radius.
- Larger containers and cards use **rounded-lg (8px)**.
- This creates a precise, industrial feel that isn't overly aggressive, maintaining the professional tone required for supply chain management.

## Components
- **Buttons:** Primary buttons use the Brand Blue (#00468C) with white text. Success actions use the Vibrant Green (#46B450). Secondary buttons should be outlined in the neutral border color.
- **Input Fields:** Use a 1px border. On focus, the border transitions to Primary Blue with a subtle 2px glow.
- **Chips & Status Badges:** Statuses like "Delivered" or "In Transit" use high-saturation backgrounds with 10% opacity of the status color (e.g., Green for Delivered) and dark-toned text for accessibility.
- **Data Tables:** Highly structured with 1px horizontal dividers. Header rows use a subtle background tint (#F1F3F5) and uppercase Mono labels for clarity.
- **Progress Indicators:** Use the Teal Accent (#009688) to track shipment stages, providing clear visual differentiation from primary interface actions.
- **Cards:** White background with a 1px neutral-200 border, used to group related logistics data.