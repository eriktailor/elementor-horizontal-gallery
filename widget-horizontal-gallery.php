<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Elementor_Horizontal_Gallery_Widget extends \Elementor\Widget_Base {

    public function get_name() { 
        return 'horizontal_scroll_gallery'; 
    }
    
    public function get_title() { 
        return 'Horizontal Scroll Gallery'; 
    }
    
    public function get_icon() { 
        return 'eicon-gallery-grid'; 
    }
    
    public function get_categories() { 
        return [ 'general' ]; 
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => 'Gallery Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'gallery_images',
            [
                'label' => 'Add Images',
                'type' => \Elementor\Controls_Manager::GALLERY,
                'default' => [],
            ]
        );

        $this->add_control(
            'visible_items',
            [
                'label' => 'Visible Items',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [ 'px' => [ 'min' => 0.5, 'max' => 5, 'step' => 0.5 ] ],
                'default' => [ 'unit' => 'px', 'size' => 1.5 ],
                'description' => 'Number of items visible in the viewport',
            ]
        );

        $this->add_control(
            'gap_width',
            [
                'label' => 'Gap Width (px)',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'default' => [ 'unit' => 'px', 'size' => 15 ],
                'selectors' => [
                    '{{WRAPPER}} .et-scroll-container' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => 'Image Height (px)',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [ 'px' => [ 'min' => 100, 'max' => 800 ] ],
                'default' => [ 'unit' => 'px', 'size' => 400 ],
                'selectors' => [
                    '{{WRAPPER}} .et-scroll-item img' => 'max-height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .et-scroll-item' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( empty( $settings['gallery_images'] ) ) {
            return;
        }

        $visible_items = ! empty( $settings['visible_items']['size'] ) ? $settings['visible_items']['size'] : 1.5;
        $gap           = ! empty( $settings['gap_width']['size'] ) ? $settings['gap_width']['size'] : 15;

        $widget_id = 'et-gallery-' . $this->get_id();
        ?>
        <div class="et-gallery-wrapper" id="<?php echo esc_attr( $widget_id ); ?>">
            <div class="et-gallery-sticky">
                <div class="et-horizontal-track">
                    <?php foreach ( $settings['gallery_images'] as $image ) : ?>
                        <div class="et-scroll-item">
                            <?php echo wp_get_attachment_image( $image['id'], 'medium_large' ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <style>
            #<?php echo esc_attr( $widget_id ); ?> {
                position: relative;
                width: 100%;
                /* height is set by JS: sticky-height + total horizontal scroll distance */
            }
            #<?php echo esc_attr( $widget_id ); ?> .et-gallery-sticky {
                position: sticky;
                top: 0;
                width: 100%;
                overflow: hidden;
            }
            #<?php echo esc_attr( $widget_id ); ?> .et-horizontal-track {
                display: flex;
                flex-wrap: nowrap;
                gap: <?php echo (int) $gap; ?>px;
                will-change: transform;
            }
            #<?php echo esc_attr( $widget_id ); ?> .et-scroll-item {
                flex: 0 0 calc((100vw / <?php echo (float) $visible_items; ?>) - <?php echo (int) $gap; ?>px);
                max-width: calc((100vw / <?php echo (float) $visible_items; ?>) - <?php echo (int) $gap; ?>px);
                overflow: hidden;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            #<?php echo esc_attr( $widget_id ); ?> .et-scroll-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
        </style>

        <script>
        (function() {
            var widgetId = '<?php echo esc_js( $widget_id ); ?>';

            function initHorizontalScroll() {
                var wrapper = document.getElementById(widgetId);
                if (!wrapper) return;

                var sticky = wrapper.querySelector('.et-gallery-sticky');
                var track  = wrapper.querySelector('.et-horizontal-track');
                if (!sticky || !track) return;

                var maxScroll = 0;

                function setup() {
                    maxScroll = Math.max(0, track.scrollWidth - sticky.clientWidth);
                    // The wrapper must be tall enough that the user scrolls through
                    // the full horizontal distance before the sticky element unsticks.
                    wrapper.style.height = (sticky.offsetHeight + maxScroll) + 'px';
                }

                function onScroll() {
                    var wrapperTop = wrapper.getBoundingClientRect().top + window.pageYOffset;
                    var scrolled   = window.pageYOffset - wrapperTop;
                    var clamped    = Math.max(0, Math.min(maxScroll, scrolled));
                    track.style.transform = 'translateX(' + (-clamped) + 'px)';
                }

                setup();
                onScroll();

                window.addEventListener('scroll', onScroll, { passive: true });
                window.addEventListener('resize', function() { setup(); onScroll(); });
            }

            if (window.elementorFrontend) {
                elementorFrontend.hooks.addAction('frontend/element_ready/horizontal_scroll_gallery.default', function($scope) {
                    if ($scope.find('#' + widgetId).length) {
                        setTimeout(initHorizontalScroll, 200);
                    }
                });
            } else {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initHorizontalScroll);
                } else {
                    initHorizontalScroll();
                }
            }
        })();
        </script>
        <?php
    }

    protected function content_template() {
        ?>
        <# if ( settings.gallery_images.length ) {
            var visibleItems = settings.visible_items.size || 1.5;
            var gap = settings.gap_width.size || 15;
        #>
            <div class="et-gallery-wrapper" id="et-gallery-{{ id }}">
                <div class="et-gallery-sticky">
                    <div class="et-horizontal-track">
                        <# _.each( settings.gallery_images, function( image ) { #>
                            <div class="et-scroll-item">
                                <img src="{{ image.url }}" alt="">
                            </div>
                        <# } ); #>
                    </div>
                </div>
            </div>

            <style>
                .elementor-element-{{ id }} .et-gallery-wrapper {
                    position: relative;
                    width: 100%;
                }
                .elementor-element-{{ id }} .et-gallery-sticky {
                    width: 100%;
                    overflow: hidden;
                }
                .elementor-element-{{ id }} .et-horizontal-track {
                    display: flex;
                    flex-wrap: nowrap;
                    gap: {{ settings.gap_width.size }}{{ settings.gap_width.unit }};
                    will-change: transform;
                }
                .elementor-element-{{ id }} .et-scroll-item {
                    flex: 0 0 calc((100% / {{ visibleItems }}) - {{ settings.gap_width.size }}{{ settings.gap_width.unit }});
                    max-width: calc((100% / {{ visibleItems }}) - {{ settings.gap_width.size }}{{ settings.gap_width.unit }});
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    overflow: hidden;
                }
                .elementor-element-{{ id }} .et-scroll-item img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    display: block;
                }
            </style>
        <# } #>
        <?php
    }
}