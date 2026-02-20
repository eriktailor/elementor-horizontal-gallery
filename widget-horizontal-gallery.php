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

        $this->add_control(
            'image_height',
            [
                'label' => 'Image Height (px)',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [ 'px' => [ 'min' => 100, 'max' => 800 ] ],
                'default' => [ 'unit' => 'px', 'size' => 400 ],
                'selectors' => [
                    '{{WRAPPER}} .et-scroll-item img' => 'max-height: {{SIZE}}{{UNIT}};',
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
                /* Height will be set by JS */
                width: 100%;
                position: relative;
            }
            .et-sticky-wrapper {
                position: sticky;
                top: 0;
                height: 100vh;
                width: 100%;
                overflow: hidden;
                display: flex;
                align-items: center;
            }
            .et-horizontal-track {
                display: flex;
                flex-wrap: nowrap;
                height: 100%;
                align-items: center;
                gap: <?php echo $gap; ?>px;
                padding: 0; /* Optional padding */
                will-change: transform;
            }
            .et-scroll-item {
                flex: 0 0 <?php echo $final_item_width; ?>%;
                /* Make it responsive based on viewport width instead of percentage of parent if needed, 
                   but percentage of parent is fine inside the track which will be wide */
                flex: 0 0 calc((100vw / <?php echo $visible_items; ?>) - <?php echo $gap; ?>px); 
                max-width: calc((100vw / <?php echo $visible_items; ?>) - <?php echo $gap; ?>px);
                
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .et-scroll-item img {
                width: 100%;
                height: auto;
                max-height: <?php echo $image_height; ?>px;
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

                var stickyWrapper = section.querySelector('.et-sticky-wrapper');
                var track = section.querySelector('.et-horizontal-track');
                
                if (!stickyWrapper || !track) return;

                var animationFrameId;

                function setDimensions() {
                    if (window.innerWidth <= 768) {
                        section.style.height = 'auto';
                        track.style.transform = 'none';
                        return;
                    }

                    var trackWidth = track.scrollWidth;
                    var viewportWidth = window.innerWidth;
                    var scrollDistance = trackWidth - viewportWidth;
                    
                    var totalHeight = window.innerHeight + scrollDistance;
                    
                    if (scrollDistance > 0) {
                        section.style.height = totalHeight + 'px';
                    } else {
                        section.style.height = 'auto';
                    }
                }

                function onScroll() {
                    if (window.innerWidth <= 768) return;

                    var rect = section.getBoundingClientRect();
                    var sectionTop = rect.top;
                    
                    var moveX = -sectionTop;
                    
                    var trackWidth = track.scrollWidth;
                    var viewportWidth = window.innerWidth;
                    var maxTranslate = trackWidth - viewportWidth;
                    
                    if (moveX < 0) moveX = 0;
                    if (moveX > maxTranslate) moveX = maxTranslate;
                    
                    track.style.transform = 'translateX(-' + moveX + 'px)';
                }

                function onScrollThrottled() {
                     if (animationFrameId) {
                         cancelAnimationFrame(animationFrameId);
                     }
                     animationFrameId = requestAnimationFrame(onScroll);
                }

                window.addEventListener('resize', setDimensions);
                window.addEventListener('scroll', onScrollThrottled);
                
                var images = track.querySelectorAll('img');
                var loadedImages = 0;
                
                if (images.length > 0) {
                    images.forEach(function(img) {
                        if (img.complete) {
                            loadedImages++;
                        } else {
                            img.addEventListener('load', function() {
                                loadedImages++;
                                if (loadedImages === images.length) {
                                    setDimensions();
                                    onScroll();
                                }
                            });
                        }
                    });
                    
                    if (loadedImages === images.length) {
                        setDimensions();
                        onScroll();
                    }
                } else {
                    setDimensions();
                    onScroll();
                }
                
                setTimeout(function() {
                    setDimensions();
                    onScroll();
                }, 500);
            }

            if ( window.elementorFrontend ) {
                elementorFrontend.hooks.addAction( 'frontend/element_ready/horizontal_scroll_gallery.default', function($scope){
                    // Check if this is the correct widget
                    if ( $scope.find('#' + widgetId).length ) {
                        initHorizontalScroll();
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
        #>
            <div class="et-scroll-wrapper">
                <div class="et-scroll-container" style="gap: {{ settings.gap_width.size }}{{ settings.gap_width.unit }};">
                    <# _.each( settings.gallery_images, function( image ) { #>
                        <div class="et-scroll-item" style="flex: 0 0 {{ finalItemWidth }}%;">
                            <img src="{{ image.url }}" alt="" style="max-height: {{ settings.image_height.size }}{{ settings.image_height.unit }};">
                        </div>
                    <# } ); #>
                </div>
            </div>
            
            <style>
                .elementor-element-{{ id }} .et-scroll-wrapper { 
                    width: 100%; 
                    overflow-x: auto; 
                    overflow-y: hidden; 
                    -webkit-overflow-scrolling: touch; 
                    scroll-behavior: smooth;
                }
                
                .elementor-element-{{ id }} .et-scroll-wrapper::-webkit-scrollbar {
                    height: 6px;
                }
                
                .elementor-element-{{ id }} .et-scroll-wrapper::-webkit-scrollbar-track {
                    background: #f1f1f1;
                }
                
                .elementor-element-{{ id }} .et-scroll-wrapper::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 3px;
                }
                
                .elementor-element-{{ id }} .et-scroll-container { 
                    display: flex; 
                    flex-direction: row; 
                    flex-wrap: nowrap; 
                    align-items: stretch; 
                    padding-bottom: 8px;
                }
                
                .elementor-element-{{ id }} .et-scroll-item { 
                    min-width: 0;
                    overflow: hidden;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .elementor-element-{{ id }} .et-scroll-item img { 
                    width: 100%; 
                    height: auto;
                    object-fit: cover;
                    display: block; 
                }
            </style>
        <# } #>
        <?php
    }
}