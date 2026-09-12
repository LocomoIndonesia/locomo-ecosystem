---
name: Obsidian Hoard
colors:
  surface: '#131313'
  surface-dim: '#131313'
  surface-bright: '#393939'
  surface-container-lowest: '#0e0e0e'
  surface-container-low: '#1c1b1b'
  surface-container: '#201f1f'
  surface-container-high: '#2a2a2a'
  surface-container-highest: '#353534'
  on-surface: '#e5e2e1'
  on-surface-variant: '#d0c6ab'
  inverse-surface: '#e5e2e1'
  inverse-on-surface: '#313030'
  outline: '#999077'
  outline-variant: '#4d4732'
  surface-tint: '#e9c400'
  primary: '#fff6df'
  on-primary: '#3a3000'
  primary-container: '#ffd700'
  on-primary-container: '#705e00'
  inverse-primary: '#705d00'
  secondary: '#66dd8b'
  on-secondary: '#003919'
  secondary-container: '#25a55a'
  on-secondary-container: '#003115'
  tertiary: '#f1f6ff'
  on-tertiary: '#003353'
  tertiary-container: '#bdddff'
  on-tertiary-container: '#206393'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffe16d'
  primary-fixed-dim: '#e9c400'
  on-primary-fixed: '#221b00'
  on-primary-fixed-variant: '#544600'
  secondary-fixed: '#83fba5'
  secondary-fixed-dim: '#66dd8b'
  on-secondary-fixed: '#00210c'
  on-secondary-fixed-variant: '#005227'
  tertiary-fixed: '#cee5ff'
  tertiary-fixed-dim: '#96ccff'
  on-tertiary-fixed: '#001d32'
  on-tertiary-fixed-variant: '#004a75'
  background: '#131313'
  on-background: '#e5e2e1'
  surface-variant: '#353534'
  obsidian-surface: '#1A1A1A'
  faction-nisnas: '#50C878'
  faction-human: '#4682B4'
  faction-android: '#DC143C'
  gold-glow: rgba(255, 215, 0, 0.4)
  glass-stroke: rgba(255, 255, 255, 0.1)
typography:
  display-hero:
    fontFamily: Playfair Display
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-md:
    fontFamily: Playfair Display
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  headline-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 28px
    fontWeight: '700'
    lineHeight: '1.2'
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  container-max: 1200px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 48px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 32px
---

## Brand & Style

This design system establishes a high-stakes, cinematic financial experience that bridges the gap between premium investment banking and MMORPG strategy. The brand personality is mysterious, elite, and adventurous—designed to evoke the feeling of a "Perburuan Harta Karun" (Treasure Hunt) where financial growth is the ultimate victory.

The aesthetic utilizes **Dark Obsidian Glassmorphism** combined with **Neumorphic tactile elements**. We employ deep charcoal layers with high-refraction blurs to create a sense of depth and mystery. High-contrast "Glowing Gold" serves as the primary driver for conversions, simulating the discovery of rare loot. The interface is grounded in a dark, atmospheric environment punctuated by particle-gold textures and sharp, glowing outlines that define the competitive landscape of the three factions.

## Colors

The palette is anchored by **Dark Obsidian (#121212)** to maintain a premium, focused atmosphere. **Glowing Gold** is reserved strictly for primary calls-to-action and critical "treasure" milestones. 

The faction system uses distinct chromatic signatures:
- **Emerald Green (Nisnas):** Prosperity and organic growth.
- **Steel Blue (Human):** Stability and traditional strategy.
- **Crimson Red (Android):** Efficiency and high-tech aggression.

Backgrounds should use a subtle gradient from #121212 to #1A1A1A to prevent visual flatlining. Interactive surfaces utilize a semi-transparent white stroke (10% opacity) to simulate the edge of polished glass.

## Typography

The typographic hierarchy balances the "Royal/Ancient" feel of **Playfair Display** with the utilitarian precision of **Inter**.

Headings use Playfair Display to signal prestige and narrative importance. For financial data and UI controls, Inter provides maximum legibility at small sizes. All uppercase labels should have increased letter spacing (5%) to mimic high-end fashion or luxury gaming interfaces. For mobile, display sizes are scaled down to ensure the "Golden" headlines do not break across too many lines.

## Layout & Spacing

This design system uses a **Fluid Grid** with generous inner-container margins to create a focused, "theatrical" center stage. 

- **Desktop:** A 12-column grid with 24px gutters. Content is centered with a max-width of 1200px to maintain a premium, editorial feel.
- **Atmospheric Spacing:** Use vertical "Stack" units (32px, 64px) to separate different game sections or faction zones.
- **Mobile:** Reflows to a single column with 16px side margins. Glass panels should extend to the edge of the screen or maintain a very small 8px margin to maximize the frosted effect.

## Elevation & Depth

Depth is achieved through a combination of **Glassmorphism** and **Neumorphic** layering:

1.  **Base Layer:** Dark Obsidian (#121212) with a faint gold dust particle overlay.
2.  **Surface Layer (Glass):** Panels use a background blur (16px - 24px) and a background color of `rgba(26, 26, 26, 0.6)`. They feature a 1px solid border at 10% white to define the glass edge.
3.  **Interactive Layer (Neumorphism):** Buttons and small controls use soft inner shadows and outer glows rather than traditional drop shadows.
4.  **Loot/Callout Layer:** Elements that represent "Treasure" or primary actions feature an outer bloom/glow effect (`box-shadow: 0 0 15px rgba(255, 215, 0, 0.3)`).

## Shapes

The shape language is **Soft (0.25rem)** to maintain a sharp, military/strategy game aesthetic while remaining modern. Large cards and glass panels should use `rounded-lg` (0.5rem) to soften the large surface areas, but interactive elements like buttons and faction badges should remain crisper to feel like precision-cut tools or stone tablets.

## Components

### Buttons
- **Primary (Treasure):** Solid Glowing Gold background, black text (Inter Bold). Box-shadow includes a 10px gold bloom.
- **Faction Secondary:** Transparent background with a 2px border matching the faction color (Green, Blue, or Red). 

### Cards
- **Glass Panel:** Heavy backdrop-filter (blur: 20px). Border is a top-to-bottom linear gradient from `rgba(255,255,255,0.2)` to `transparent`.
- **Active State:** When hovered, the border color transitions to the primary Gold or the relevant Faction color.

### Inputs
- Neumorphic style: Inset shadows on a dark surface to create a "carved" look into the obsidian background. Labels sit above in Inter (Label-sm).

### Progress Bars (MMORPG Style)
- Thick bars with faction-colored fills. The "fill" should have a slight inner glow to look like a liquid energy source.

### Icons
- Minimalist line icons (Material Symbols Outlined) with a consistent 1.5px stroke width. Icons should be tinted in faction colors or gold when active.