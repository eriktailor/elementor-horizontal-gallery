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
        $gap = ! empty( $settings['gap_width']['size'] ) ? $settings['gap_width']['size'] : 15;
        $image_height = ! empty( $settings['image_height']['size'] ) ? $settings['image_height']['size'] : 400;
        
        // Calculate item width based on visible items
        $item_width = 100 / $visible_items;
        $total_gaps = $visible_items - 1;
        $gap_space = ( $total_gaps * $gap ) / $visible_items;
        $final_item_width = $item_width - $gap_space;
        
        $widget_id = 'et-gallery-' . $this->get_id();
        ?>
        <div class="et-gallery-section" id="<?php echo esc_attr($widget_id); ?>">
            <div class="et-sticky-wrapper">
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
            .et-gallery-section {
                width: 100%;
                position: relative;
                overflow: hidden; /* Hide the track that sticks out */
            }
            .et-sticky-wrapper {
                width: 100%;
                overflow: hidden;
                display: flex;
                align-items: flex-start;
            }
            .et-horizontal-track {
                display: flex;
                flex-wrap: nowrap;
                height: 100%; /* Ensure track takes full height if wrapper has height */
                align-items: stretch; /* Stretch items to full height */
                gap: <?php echo $gap; ?>px;
                padding: 0;
                will-change: transform;
            }
            .et-scroll-item {
                flex: 0 0 <?php echo $final_item_width; ?>%;
                /* Make it responsive based on viewport width instead of percentage of parent if needed, 
                   but percentage of parent is fine inside the track which will be wide */
                flex: 0 0 calc((100vw / <?php echo $visible_items; ?>) - <?php echo $gap; ?>px); 
                max-width: calc((100vw / <?php echo $visible_items; ?>) - <?php echo $gap; ?>px);
                /* Height handled by Elementor responsive controls now */
                display: flex;
                flex-direction: column;
                justify-content: center;
                overflow: hidden;
            }
            .et-scroll-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
        </style>

        <script>
        (function($) {
            var widgetId = '<?php echo esc_js($widget_id); ?>';
            
            function initHorizontalScroll() {
                var section = document.getElementById(widgetId);
                if (!section) return;

                var track = section.querySelector('.et-horizontal-track');
                if (!track) return;

                // State
                var currentScrollX = 0;

                // Update track position
                function updateTrack() {
                    track.style.transform = 'translateX(' + (-currentScrollX) + 'px)';
                }

                // Handle both Mouse Wheel and Touch
                function handleScrollMotion(delta, e) {
                    var trackWidth = track.scrollWidth;
                    var containerWidth = section.clientWidth;
                    var maxScroll = trackWidth - containerWidth;

                    // If content fits, no horizontal scroll needed
                    if (maxScroll <= 0) return;

                    var isScrollingDown = delta > 0;
                    var isScrollingUp = delta < 0;

                    // Check bounds
                    // currentScrollX might slightly drift due to float math, so use a small epsilon if needed, 
                    // but integers are usually fine here.
                    var atStart = currentScrollX <= 0;
                    var atEnd = currentScrollX >= maxScroll;

                    var shouldIntercept = false;

                    // If scrolling down (moving forward) and we are not at the end
                    if (isScrollingDown && !atEnd) {
                        shouldIntercept = true;
                    } 
                    // If scrolling up (moving backward) and we are not at the start
                    else if (isScrollingUp && !atStart) {
                        shouldIntercept = true;
                    }

                    if (shouldIntercept) {
                        // Crucial: prevent page scroll
                        if (e.cancelable !== false) {
                            e.preventDefault();
                            e.stopPropagation(); 
                        }
                        
                        currentScrollX += delta;

                        // Clamp values
                        if (currentScrollX < 0) currentScrollX = 0;
                        if (currentScrollX > maxScroll) currentScrollX = maxScroll;

                        updateTrack();
                    }
                }

                function onWheel(e) {
                    handleScrollMotion(e.deltaY, e);
                }
                
                // Touch handling
                var touchStartY = 0;
                
                section.addEventListener('touchstart', function(e) {
                    touchStartY = e.touches[0].clientY;
                }, { passive: false });
                
                section.addEventListener('touchmove', function(e) {
                    var touchCurrentY = e.touches[0].clientY;
                    var deltaY = touchStartY - touchCurrentY; // Down drag = negative diff, but we want positive delta for "scrolling down"
                    
                    // Update for next move
                    touchStartY = touchCurrentY;
                    
                    handleScrollMotion(deltaY, e);
                    
                }, { passive: false });

                // Bind wheel event to the SECTION itself. 
                section.addEventListener('wheel', onWheel, { passive: false });
            }

            if ( window.elementorFrontend ) {
                elementorFrontend.hooks.addAction( 'frontend/element_ready/horizontal_scroll_gallery.default', function($scope){
                    if ( $scope.find('#' + widgetId).length ) {
                        setTimeout(function(){
                            // Re-init on images loaded to ensure width is correct? 
                            // Though scrollWidth usually updates automatically.
                            jQuery(document).ready(initHorizontalScroll); 
                        }, 200);
                    }
                });
            } else {
                jQuery(document).ready(initHorizontalScroll);
            }
        })(jQuery);
        </script>
        <?php
    }

    protected function content_template() {
        ?>
        <# if ( settings.gallery_images.length ) { 
            var visibleItems = settings.visible_items.size || 1.5;
            var gap = settings.gap_width.size || 15;
            var itemWidth = 100 / visibleItems;
            var totalGaps = visibleItems - 1;
            var gapSpace = (totalGaps * gap) / visibleItems;
            var finalItemWidth = itemWidth - gapSpace;
            // Removed imageHeight variable as it's handled by responsive controls
        #>
            <div class="et-gallery-section" id="et-gallery-{{ id }}">
                <div class="et-sticky-wrapper">
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
                .elementor-element-{{ id }} .et-gallery-section {
                    width: 100%;
                    position: relative;
                    overflow: hidden;
                }
                .elementor-element-{{ id }} .et-sticky-wrapper {
                    width: 100%;
                    overflow: hidden;
                    display: flex;
                    align-items: flex-start;
                }
                .elementor-element-{{ id }} .et-horizontal-track {
                    display: flex;
                    flex-wrap: nowrap;
                    height: 100%;
                    align-items: stretch;
                    gap: {{ settings.gap_width.size }}{{ settings.gap_width.unit }};
                    padding: 0;
                    will-change: transform;
                }
                
                @media (max-width: 768px) {
                    .elementor-element-{{ id }} .et-sticky-wrapper {
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                        scrollbar-width: none;
                    }
                    .elementor-element-{{ id }} .et-sticky-wrapper::-webkit-scrollbar { 
                        display: none;
                    }
                }

                .elementor-element-{{ id }} .et-scroll-item {
                    flex: 0 0 {{ finalItemWidth }}%;
                    flex: 0 0 calc((100% / {{ visibleItems }}) - {{ settings.gap_width.size }}{{ settings.gap_width.unit }});
                    max-width: calc((100% / {{ visibleItems }}) - {{ settings.gap_width.size }}{{ settings.gap_width.unit }});
                    /* Height handled by Elementor responsive controls */
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