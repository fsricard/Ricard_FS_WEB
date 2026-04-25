    <script>
        // Registro del módulo personalizado del dropdown de iconos para Quill
        class DiablilloIcons {
            constructor(quill, options) {
                this.quill = quill;
                this.options = options;

                // Localizar el botón en la toolbar
                const toolbar = quill.getModule('toolbar');
                const button = toolbar.container.querySelector('.ql-diablillo');

                // Crear el contenedor del dropdown
                const dropdown = document.createElement('div');
                dropdown.classList.add('diablillo-dropdown');

                // Rellenar el dropdown con iconos
                options.icons.forEach(iconClass => {
                    const btn = document.createElement('button');
                    btn.classList.add('diablillo-icon-btn');
                    btn.innerHTML = `<i class="${iconClass}"></i>`;
                    btn.onclick = () => this.insertIcon(iconClass);
                    dropdown.appendChild(btn);
                });

                // Insertar el dropdown dentro del botón
                button.appendChild(dropdown);

                // Animación toggle
                button.addEventListener('click', e => {
                    e.preventDefault();
                    dropdown.classList.toggle('open');
                });
            }

            insertIcon(iconClass) {
                const range = this.quill.getSelection();
                if (range) {
                    this.quill.insertEmbed(range.index, 'icon', iconClass);
                    this.quill.setSelection(range.index + 1);
                }
            }
        }

        Quill.register('modules/diablilloIcons', DiablilloIcons);

        // Selector de tamaño para iconos
        class DiablilloIconSize {
            constructor(quill, options) {
                this.quill = quill;
                this.options = options;

                const toolbar = quill.getModule('toolbar');
                const button = toolbar.container.querySelector('.ql-iconSize');

                const dropdown = document.createElement('div');
                dropdown.classList.add('diablillo-dropdown');

                options.sizes.forEach(sizeClass => {
                    const btn = document.createElement('button');
                    btn.classList.add('diablillo-icon-btn');
                    btn.innerHTML = `<i class="fa-solid fa-face-angry-horns ${sizeClass}"></i>`;
                    btn.onclick = () => this.applySize(sizeClass);
                    dropdown.appendChild(btn);
                });

                button.appendChild(dropdown);

                button.addEventListener('click', e => {
                    e.preventDefault();
                    dropdown.classList.toggle('open');
                });
            }

            applySize(sizeClass) {
                if (!selectedIcon) return;

                const icon = selectedIcon.querySelector('i');

                // Eliminar tamaños previos
                icon.classList.remove('fa-xs', 'fa-sm', 'fa-lg', 'fa-2x', 'fa-3x');

                // Aplicar nuevo tamaño
                icon.classList.add(sizeClass);

                // Actualizar data-icon con el tamaño incluido
                const classes = icon.className;
                selectedIcon.setAttribute('data-icon', classes);
            }
        }

        Quill.register('modules/diablilloIconSize', DiablilloIconSize);

        // Iconos como EMBED
        const Embed = Quill.import('blots/embed');

        class IconBlot extends Embed {
            static create(className) {
                const node = super.create();

                // className ya incluye tamaño si existe
                node.innerHTML = `<i class="${className}"></i>`;
                node.setAttribute('data-icon', className);

                return node;
            }

            static value(node) {
                return node.getAttribute('data-icon');
            }
        }

        IconBlot.blotName = 'icon';
        IconBlot.tagName = 'span';
        IconBlot.className = 'ql-icon';

        Quill.register(IconBlot);

        // Inicialización de Quill
        var quill = new Quill('#editor-descripcion', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'font': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['blockquote'],
                    ['link'],
                    ['clean'],
                    ['diablillo'],
                    ['iconSize']
                ],
                diablilloIcons: {
                    icons: [
                        'fa-solid fa-angel',
                        'fa-solid fa-face-smile-halo',
                        'fa-solid fa-face-angry-horns',
                        'fa-solid fa-fire',
                        'fa-solid fa-skull',
                        'fa-solid fa-hand-horns',
                        'fa-solid fa-bat',
                        'fa-solid fa-candle-holder',
                        'fa-solid fa-cat',
                        'fa-solid fa-coffin-cross',
                        'fa-solid fa-crow',
                        'fa-solid fa-flask-round-potion',
                        'fa-solid fa-ghost',
                        'fa-solid fa-skull-crossbones',
                        'fa-solid fa-tombstone',
                        'fa-solid fa-scythe',
                        'fa-solid fa-book-skull'
                    ]
                },
                diablilloIconSize: {
                    sizes: ['fa-xs', 'fa-sm', 'fa-lg', 'fa-2x', 'fa-3x']
                }
            }
        });

        // Tooltip traducidos
        const tooltips = {
            'bold': 'Negrita',
            'italic': 'Cursiva',
            'underline': 'Subrayado',
            'strike': 'Tachado',
            'header': 'Tamaño de letra',
            'font': 'Tipografía',
            'list': 'Lista',
            'align': 'Alinear',
            'color': 'Color de letra',
            'background': 'Color de fondo',
            'blockquote': 'Cita / Bloque de cita',
            'link': 'Enlace',
            'clean': 'Quitar formato',
            'diablillo': 'Iconos infernales'
        };

        document.querySelectorAll('.ql-toolbar button, .ql-toolbar span').forEach(el => {
            let format = el.className.match(/ql-(\w+)/);
            if (format && tooltips[format[1]]) {
                el.setAttribute('title', tooltips[format[1]]);
                el.setAttribute('aria-label', tooltips[format[1]]);
            }
        });

        let selectedIcon = null;
        

        quill.root.addEventListener('click', function(e) {
            const icon = e.target.closest('.ql-icon i');

            // Si se hace clic en un icono
            if (icon) {
                // Quitar selección previa
                document.querySelectorAll('.ql-icon.selected').forEach(el => {
                    el.classList.remove('selected');
                });

                // Marcar el icono actual
                const wrapper = icon.closest('.ql-icon');
                wrapper.classList.add('selected');

                // Guardarlo como icono seleccionado
                selectedIcon = wrapper;
            }
        });

        quill.root.addEventListener('click', function(e) {
            if (!e.target.closest('.ql-icon')) {
                document.querySelectorAll('.ql-icon.selected').forEach(el => {
                    el.classList.remove('selected');
                });
                selectedIcon = null;
            }
        });

        // Sincronización con textarea
        const textarea = document.getElementById('descripcion');

        quill.on('text-change', function() {
            textarea.value = quill.root.innerHTML;
        });

        document.getElementById('btn-guardar').addEventListener('click', function() {
            textarea.value = quill.root.innerHTML;
        });
    </script>

</body>
</html>