<?php
/**
 * Plugin Name:       Nascor Carta Mágica
 * Plugin URI:        https://nascor.ar
 * Description:       Stack interactivo de 3 cartas mágicas con movimientos 3D, tilt por mouse, shine holográfico y panel de control.
 * Version:           2.0.0
 * Author:            NASCOR Estudio Creativo
 * Author URI:        https://nascor.ar
 * License:           GPL v2 or later
 * Text Domain:       nascor-carta-magica
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Nascor_Carta_Magica_Plugin' ) ) {

    class Nascor_Carta_Magica_Plugin {

        private static $assets_loaded = false;

        public function __construct() {
            // Registrar shortcode
            add_shortcode( 'carta_magica', [ $this, 'render_shortcode' ] );

            // Panel de administrador
            add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
            add_action( 'admin_init', [ $this, 'register_settings' ] );
        }

        /**
         * ==========================================
         * 1. PANEL DE ADMINISTRADOR
         * ==========================================
         */
        public function add_admin_menu() {
            add_menu_page(
                'Nascor Carta Mágica',
                'Carta Mágica',
                'manage_options',
                'nascor-carta-magica',
                [ $this, 'admin_page_html' ],
                'dashicons-images-alt2',
                23
            );
        }

        public function register_settings() {
            $settings = [
                'ncm_img1', 'ncm_img2', 'ncm_img3', 
                'ncm_width', 'ncm_height', 'ncm_shadow_color'
            ];
            foreach ($settings as $setting) {
                register_setting( 'nascor_cm_settings', $setting );
            }
        }

        public function admin_page_html() {
            if ( ! current_user_can( 'manage_options' ) ) return;

            // Valores por defecto
            $defaults = [
                'ncm_img1'         => 'https://nascor.ar/wp-content/uploads/2026/04/ariana-huggins-ilustraciones-3.avif',
                'ncm_img2'         => 'https://nascor.ar/wp-content/uploads/2026/04/ariana-huggins-ilustraciones-2.avif',
                'ncm_img3'         => 'https://nascor.ar/wp-content/uploads/2026/04/ariana-huggins-ilustraciones.avif',
                'ncm_width'        => '372px',
                'ncm_height'       => '607px',
                'ncm_shadow_color' => '#8b5cf6' // Morado mágico por defecto
            ];

            $options = [];
            foreach ($defaults as $key => $default_val) {
                $options[$key] = get_option($key, $default_val);
            }
            ?>
            <div class="wrap">
                <h1>Configuración de Carta Mágica 3D</h1>
                
                <div style="background: #fff; padding: 15px 20px; border-left: 4px solid #8b5cf6; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                    <h3>📌 Instrucciones</h3>
                    <p>Usa el shortcode base para mostrar las cartas con la configuración guardada aquí:</p>
                    <p><code style="font-size: 16px; padding: 5px 10px; background: #f0f0f1;">[carta_magica]</code></p>
                    <p><strong>Truco Pro:</strong> Puedes sobrescribir estas opciones en cualquier página pasando atributos al shortcode, ejemplo:<br>
                    <code>[carta_magica width="250px" height="400px" img1="url.jpg"]</code></p>
                </div>

                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 350px; background: #fff; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                        <form method="post" action="options.php">
                            <?php settings_fields( 'nascor_cm_settings' ); ?>
                            
                            <h3 style="background:#f0f0f1; padding:10px;">📏 Dimensiones y Diseño</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Ancho (Width)</th>
                                    <td><input type="text" name="ncm_width" value="<?php echo esc_attr( $options['ncm_width'] ); ?>" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Altura (Height)</th>
                                    <td><input type="text" name="ncm_height" value="<?php echo esc_attr( $options['ncm_height'] ); ?>" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Color de Sombra/Aura Mágica</th>
                                    <td><input type="color" name="ncm_shadow_color" value="<?php echo esc_attr( $options['ncm_shadow_color'] ); ?>" /></td>
                                </tr>
                            </table>

                            <h3 style="background:#f0f0f1; padding:10px;">🖼️ Imágenes por Defecto</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Imagen Frontal (Carta 1)</th>
                                    <td><input type="url" name="ncm_img1" value="<?php echo esc_url( $options['ncm_img1'] ); ?>" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Imagen Medio (Carta 2)</th>
                                    <td><input type="url" name="ncm_img2" value="<?php echo esc_url( $options['ncm_img2'] ); ?>" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Imagen Fondo (Carta 3)</th>
                                    <td><input type="url" name="ncm_img3" value="<?php echo esc_url( $options['ncm_img3'] ); ?>" class="regular-text" /></td>
                                </tr>
                            </table>

                            <?php submit_button('Guardar y Ver Cambios'); ?>
                        </form>
                    </div>

                    <div style="flex: 1; min-width: 400px;">
                        <h3>👁️ Vista Previa Interactiva</h3>
                        <div style="padding: 20px; background: #e0e0e0; border-radius: 8px; display: flex; justify-content: center; overflow: hidden;">
                            <?php echo do_shortcode('[carta_magica]'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * ==========================================
         * 2. RENDERIZADO DEL SHORTCODE
         * ==========================================
         */
        public function render_shortcode( $atts ) {
            // Obtenemos los valores por defecto del panel administrador
            $def_img1   = get_option('ncm_img1', '');
            $def_img2   = get_option('ncm_img2', '');
            $def_img3   = get_option('ncm_img3', '');
            $def_width  = get_option('ncm_width', '372px');
            $def_height = get_option('ncm_height', '607px');
            $shadow_hex = get_option('ncm_shadow_color', '#8b5cf6');

            // Combinamos con los atributos del shortcode (el shortcode tiene prioridad)
            $atts = shortcode_atts( array(
                'img1'   => $def_img1, 
                'img2'   => $def_img2,
                'img3'   => $def_img3,
                'width'  => $def_width,
                'height' => $def_height,
            ), $atts, 'carta_magica' );

            if ( empty( $atts['img1'] ) || empty( $atts['img2'] ) || empty( $atts['img3'] ) ) {
                return '<p style="color:#e11d48; font-weight:bold;">⚠️ Carta Mágica: Configura las 3 imágenes en el panel o pásalas por shortcode.</p>';
            }

            // Convertir Hex a RGB para inyectar en variables CSS
            list($r, $g, $b) = sscanf($shadow_hex, "#%02x%02x%02x");
            $shadow_rgb = "$r, $g, $b";

            $unique_id = 'nascor-cm-' . wp_rand( 100000, 999999 );

            ob_start();
            
            // Imprimir CSS/JS solo la primera vez que se llama al shortcode en la página
            if ( ! self::$assets_loaded ) {
                $this->print_assets();
                self::$assets_loaded = true;
            }
            ?>

            <div id="<?php echo esc_attr( $unique_id ); ?>" class="nascor-carta-magica-stack" 
                 style="width: <?php echo esc_attr( $atts['width'] ); ?>; 
                        height: <?php echo esc_attr( $atts['height'] ); ?>; 
                        --ncm-shadow-rgb: <?php echo esc_attr( $shadow_rgb ); ?>;">
                
                <div class="nascor-card" style="background-image: url('<?php echo esc_url( $atts['img1'] ); ?>');" data-index="0"></div>
                
                <div class="nascor-card" style="background-image: url('<?php echo esc_url( $atts['img3'] ); ?>');" data-index="1"></div>
                     
                <div class="nascor-card" style="background-image: url('<?php echo esc_url( $atts['img2'] ); ?>');" data-index="2"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        /**
         * ==========================================
         * 3. CSS Y JS (OPTMIZADOS Y ENCAPSULADOS)
         * ==========================================
         */
        private function print_assets() {
            ?>
            <style>
                .nascor-carta-magica-stack {
                    position: relative;
                    perspective: 2200px;
                    transform-style: preserve-3d;
                    cursor: grab;
                    user-select: none;
                    max-width: 100%; 
                    margin: 60px auto;
                }

                .nascor-card {
                    position: absolute;
                    inset: 0;
                    background-size: cover;
                    background-position: center;
                    border-radius: 22px;
                    box-shadow: 
                        0 25px 50px -12px rgba(0, 0, 0, 0.35),
                        0 0 0 1px rgba(255, 255, 255, 0.15) inset,
                        0 8px 25px -8px rgba(var(--ncm-shadow-rgb), 0.4);
                    border: 2px solid rgba(255,255,255, 0.2);
                    overflow: hidden;
                    transition: 
                        transform 850ms cubic-bezier(0.34, 1.56, 0.64, 1),
                        box-shadow 850ms cubic-bezier(0.34, 1.56, 0.64, 1),
                        filter 400ms ease;
                    cursor: pointer;
                    z-index: 10;
                    box-sizing: border-box;
                }

                /* Shine holográfico mágico */
                .nascor-card::before {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(
                        125deg,
                        rgba(255,255,255,0) 30%,
                        rgba(255,255,255,0.45) 45%,
                        rgba(255,255,255,0) 65%
                    );
                    opacity: 0;
                    transform: skewX(-20deg);
                    pointer-events: none;
                    transition: all 0.6s ease;
                }

                .nascor-card:hover::before {
                    opacity: 1;
                    animation: nascor-shine 1.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
                }

                @keyframes nascor-shine {
                    0%   { transform: translateX(-150%) skewX(-20deg); }
                    100% { transform: translateX(300%) skewX(-20deg); }
                }

                .nascor-card.top-card {
                    box-shadow: 
                        0 30px 60px -15px rgba(var(--ncm-shadow-rgb), 1),
                        0 0 35px 8px rgba(var(--ncm-shadow-rgb), 0.5),
                        0 25px 50px -12px rgba(0, 0, 0, 0.4);
                    filter: brightness(1.08) contrast(1.05);
                    transform: scale(1.03) !important;
                }

                .nascor-card.back-1 { filter: brightness(0.88) contrast(0.95); }
                .nascor-card.back-2 { filter: brightness(0.78) contrast(0.90); }

                .nascor-carta-magica-stack::after {
                    content: '';
                    position: absolute;
                    bottom: -18px;
                    left: 50%;
                    width: 88%;
                    height: 22px;
                    background: radial-gradient(ellipse at center, rgba(0,0,0,0.25) 0%, transparent 70%);
                    border-radius: 50%;
                    transform: translateX(-50%);
                    z-index: -1;
                    filter: blur(8px);
                    pointer-events: none;
                }
            </style>

            <script>
                (function() {
                    function initNascorMagicCards() {
                        const stacks = document.querySelectorAll('.nascor-carta-magica-stack');
                        
                        stacks.forEach(stack => {
                            // Prevenir reinicialización
                            if(stack.dataset.initialized) return;
                            stack.dataset.initialized = "true";

                            const cards = Array.from(stack.querySelectorAll('.nascor-card'));
                            if (cards.length !== 3) return;

                            let order = [0, 1, 2];

                            function updatePositions() {
                                const containerWidth = stack.offsetWidth;
                                const offsetX = containerWidth * 0.085;
                                const offsetY = containerWidth * 0.059;

                                cards.forEach((card, i) => {
                                    const position = order.indexOf(i);
                                    
                                    const translateX = position * offsetX;
                                    const translateY = position * offsetY;
                                    const rotateZ   = position * 5.5;
                                    const scale     = 1 - (position * 0.065);
                                    
                                    card.style.transform = `
                                        translate(${translateX}px, ${translateY}px) 
                                        rotateZ(${rotateZ}deg) 
                                        scale(${scale})
                                        translateZ(${30 - position * 12}px)
                                    `;
                                    
                                    card.style.zIndex = 100 - (position * 30);
                                    
                                    card.classList.remove('top-card', 'back-1', 'back-2');
                                    if (position === 0) card.classList.add('top-card');
                                    else if (position === 1) card.classList.add('back-1');
                                    else card.classList.add('back-2');
                                });
                            }

                            // Evitar errores si el contenedor no tiene ancho todavía (por estar oculto)
                            requestAnimationFrame(updatePositions);

                            cards.forEach(card => {
                                card.addEventListener('click', function (e) {
                                    e.stopImmediatePropagation();
                                    const clickedIndex = parseInt(this.getAttribute('data-index'));
                                    if (order[0] === clickedIndex) return;

                                    this.style.transitionDuration = '320ms';
                                    this.style.transform += ' scale(1.12) translateY(-38px) rotateZ(8deg)';

                                    setTimeout(() => {
                                        order = order.filter(idx => idx !== clickedIndex);
                                        order.unshift(clickedIndex);
                                        this.style.transitionDuration = '850ms';
                                        updatePositions();
                                    }, 340);
                                });
                            });

                            let isDragging = false;
                            let startX = 0;

                            stack.addEventListener('mousedown', e => { isDragging = true; startX = e.clientX; stack.style.cursor = 'grabbing'; });
                            window.addEventListener('mouseup', () => {
                                if (isDragging) {
                                    isDragging = false;
                                    stack.style.cursor = 'grab';
                                    stack.style.transition = 'transform 600ms cubic-bezier(0.23, 1, 0.32, 1)';
                                    stack.style.transform = 'perspective(2200px) rotateY(0deg) rotateX(0deg)';
                                }
                            });

                            stack.addEventListener('mousemove', e => {
                                const rect = stack.getBoundingClientRect();
                                if (!isDragging) {
                                    const xPercent = (e.clientX - rect.left) / rect.width;
                                    const yPercent = (e.clientY - rect.top) / rect.height;
                                    const rotateY = (xPercent - 0.5) * 18;
                                    const rotateX = (0.5 - yPercent) * 14;
                                    stack.style.transition = 'transform 120ms linear';
                                    stack.style.transform = `perspective(2200px) rotateY(${rotateY}deg) rotateX(${rotateX}deg)`;
                                    return;
                                }
                                const moveX = (e.clientX - startX) / rect.width * 22;
                                const moveY = (e.clientY - rect.top - rect.height / 2) / rect.height * -18;
                                stack.style.transition = 'none';
                                stack.style.transform = `perspective(2200px) rotateY(${moveX}deg) rotateX(${moveY}deg)`;
                            });

                            stack.addEventListener('mouseleave', () => {
                                if (!isDragging) {
                                    stack.style.transition = 'transform 600ms cubic-bezier(0.23, 1, 0.32, 1)';
                                    stack.style.transform = 'perspective(2200px) rotateY(0deg) rotateX(0deg)';
                                }
                            });
                        });
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initNascorMagicCards);
                    } else {
                        initNascorMagicCards();
                    }
                })();
            </script>
            <?php
        }
    }

    new Nascor_Carta_Magica_Plugin();
}