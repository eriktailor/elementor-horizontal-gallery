# Elementor Horizontal Scroll Gallery Plugin

A custom Elementor widget that displays a gallery where images scroll horizontally (driven by the user's vertical scroll/swipe), rather than vertically with the page.

---

## Files

| File | Description |
|------|-------------|
| `elementor-horizontal-gallery.php` | Thin plugin entry point; registers the widget via the `elementor/widgets/register` hook. |
| `widget-horizontal-gallery.php` | Everything else: Elementor controls, PHP render output, inline CSS, inline JS, and the live editor JS template. |

---

## Elementor Controls

| Control | Type | Default | Purpose |
|---------|------|---------|---------|
| `gallery_images` | Gallery | — | Multi-image picker |
| `visible_items` | Slider (0.5–5) | 1.5 | How many images are visible in the viewport at once |
| `gap_width` | Slider (0–100px) | 15px | Gap between items |
| `image_height` | Responsive Slider (100–800px) | 400px | Height of each gallery item, responsive per breakpoint |

---

## How the Scroll Works

- Images are laid out in a `flex: nowrap` row inside `.et-horizontal-track`.
- Each item's width is `calc((100vw / visible_items) - gap_px)`.
- Scrolling is done via CSS `translateX` — the widget intercepts `wheel` and `touchmove` events on the section, converts vertical delta into a horizontal offset, and prevents page scroll when the gallery isn't at its start/end bounds.
- No native `overflow: scroll` is used on desktop (mobile falls back to `overflow-x: auto`).

---

## Notable Quirks

- Inline `<style>` and `<script>` are output directly in `render()` per widget instance (not enqueued).
- There's dead CSS: the first `flex: 0 0 X%` on `.et-scroll-item` is immediately overridden by the `calc(100vw ...)` line below it.
- The live editor preview (`content_template`) uses `100%` instead of `100vw` for item widths, so editor preview and frontend may differ slightly.
