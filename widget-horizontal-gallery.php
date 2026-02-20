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
        
        // Calculate total gap space
        $total_gaps = $visible_items - 1;
        $gap_space = ( $total_gaps * $gap ) / $visible_items;
        
        // Final item width accounting for gaps
        $final_item_width = $item_width - $gap_space;
        
        ?>
        <div class="et-scroll-wrapper">
            <div class="et-scroll-container">
                <?php foreach ( $settings['gallery_images'] as $image ) : ?>
                    <div class="et-scroll-item">
                        <?php echo wp_get_attachment_image( $image['id'], 'medium_large' ); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
            .et-scroll-wrapper { 
                width: 100%; 
                overflow-x: auto; 
                overflow-y: hidden; 
                -webkit-overflow-scrolling: touch; 
                scrollbar-width: thin; 
                scroll-behavior: smooth;
            }
            
            .et-scroll-wrapper::-webkit-scrollbar {
                height: 6px;
            }
            
            .et-scroll-wrapper::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            
            .et-scroll-wrapper::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 3px;
            }
            
            .et-scroll-wrapper::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
            
            .et-scroll-container { 
                display: flex; 
                flex-direction: row; 
                flex-wrap: nowrap; 
                align-items: stretch;
                gap: <?php echo $gap; ?>px;
                padding-bottom: 8px;
            }
            
            .et-scroll-item { 
                flex: 0 0 <?php echo $final_item_width; ?>%; 
                min-width: 0;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .et-scroll-item img { 
                height: auto; 
                max-height: <?php echo $image_height; ?>px; 
                width: 100%; 
                object-fit: cover;
                display: block; 
            }
            
            @media (max-width: 1024px) {
                .et-scroll-wrapper {
                    overflow-x: scroll;
                }
            }
            
            @media (max-width: 768px) {
                .et-scroll-container {
                    gap: <?php echo max(10, $gap - 5); ?>px;
                }
            }
        </style>
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