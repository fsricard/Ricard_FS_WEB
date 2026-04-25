        <footer class="container-footer">
            <div class="footer">
                <div class="sections-footer">
                    <div class="sections-footer-left">
                        <h3>Sobre el Diablillo</h3>
                        <ul>
                            <li><a href="<?= asset('/sobre-mi') ?>">Sobre mí</a></li>
                            <li><a href="<?= asset('/contacto') ?>">Contacta con el loco</a></li>
                            <li><a href="<?= asset('/politica-de-privacidad') ?>">Política de privacidad</a></li>
                        </ul>
                    </div>
        
                    <div class="sections-footer-center">
                        <h3>Yo soy el Diablillo</h3>
                        <ul>
                            <li><a href="<?= asset('/blog') ?>">El blog del loco</a></li>
                            <li><a href="<?= asset('/categorias') ?>">Categorías de mi blog</a></li>
                        </ul>
                    </div>
        
                    <div class="sections-footer-right">
                        <img src="<?= asset('/img/logo_0001.png') ?>" alt="Logotipo de el Diablillo" class="footer-logo" />
                    </div>
                </div>
        
                <hr />
        
                <h4>
                    <?php
                        echo CopyrightRicardFS();
                    ?>
                </h4>
            </div>
        </footer>

        <!-- Contenedor para el botón "Volver arriba" -->
        <button id="infernal-top-btn" aria-label="Subir arriba">
            <i class="fa-solid fa-hand-horns"></i>
        </button>

        <!-- Contenedor para el loader -->
        <div class="loader-overlay" id="loader" style="display:none;">
            <div class="loader-diablillo"></div>
        </div>

        <!-- Contenedor para el aviso de cookies -->
        <div id="cookies-infernal" class="cookies-modal">
            <div class="cookies-content">

                <div class="cookies-header">
                    <img src="<?= asset('/img/logo_0001.png') ?>" alt="Diablillo" class="cookies-logo">
                </div>

                <h3 class="cookies-title">
                    <i class="fa-solid fa-fire"></i> Aviso de Cookies Infernal <i class="fa-solid fa-fire"></i>
                </h3>

                <p class="cookies-text">
                    En este rincón del inframundo utilizamos cookies para mejorar tu experiencia,
                    analizar el flujo de almas visitantes y mantener el fuego siempre encendido.
                    Puedes leer más en nuestra
                    <a href="<?= asset('/politica-de-privacidad') ?>">Política de Privacidad</a>.
                </p>

                <button id="cookies-aceptar" class="cookies-btn">
                    Aceptar y continuar <i class="fa-solid fa-fire"></i>
                </button>

            </div>
        </div>

        <script>
            // Script para el menú responsive
            function toggleMobileMenu() {
                const menu = document.getElementById('menuMovil');
                menu.style.left = (menu.style.left === '0px') ? '-100%' : '0px';
            }

            // Script para mostrar el Loader automáticamente
            // Mostrar el Loader
            function mostrarLoader() {
                const loader = document.getElementById('loader');
                if (loader) loader.style.display = 'flex';
            }
            // Ocultar el Loader
            function ocultarLoader() {
                const loader = document.getElementById('loader');
                if (loader) loader.style.display = 'none';
            }
            // Mostrar y volver a ocultar el Loader
            function cambiarPaginaConLoader(callback) {
                mostrarLoader();

                setTimeout(() => {
                    ocultarLoader();
                    if (typeof callback === "function") callback();
                }, 2000);
            }
            // Mostrar el Loader automáticamente al entrar en una pagina nueva
            document.addEventListener("DOMContentLoaded", () => {
                const enlaces = document.querySelectorAll("a:not([target='_blank'])");

                enlaces.forEach(enlace => {
                    enlace.addEventListener("click", function(e) {
                        const url = this.href;

                        // Evita navegación inmediata
                        e.preventDefault();

                        cambiarPaginaConLoader(() => {
                            window.location.href = url;
                        });
                    });
                });
            });

            // Script para el aviso de cookies
            document.addEventListener("DOMContentLoaded", () => {

                const modal = document.getElementById("cookies-infernal");
                const btnAceptar = document.getElementById("cookies-aceptar");

                // Mostrar solo si no se ha aceptado antes
                if (!localStorage.getItem("cookiesInfernalAceptadas")) {
                    modal.style.display = "flex";
                    setTimeout(() => modal.style.opacity = "1", 50);
                }

                btnAceptar.addEventListener("click", () => {
                    localStorage.setItem("cookiesInfernalAceptadas", "true");
                    modal.style.opacity = "0";
                    setTimeout(() => modal.style.display = "none", 500);
                });

            });

            // Script àra el botón "Volver arriba"
            (function() {
                const btn = document.getElementById('infernal-top-btn');

                window.addEventListener('scroll', () => {
                    if (window.scrollY > 300) {
                        btn.classList.add('visible');
                    } else {
                        btn.classList.remove('visible');
                    }
                });

                btn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            })();
        </script>
    </body>
</html>