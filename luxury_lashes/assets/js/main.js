// assets/js/main.js - JavaScript principal del sistema Luxury Lashes

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    
    // Función para mostrar notificaciones
    window.showNotification = function(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 300px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Mostrar notificación
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Ocultar después del tiempo especificado
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
    };
    
    // Función para confirmar acciones importantes
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };
    
    // Validaciones comunes
    window.validators = {
        cedula: function(cedula) {
            return /^[0-9]{6,12}$/.test(cedula);
        },
        
        email: function(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },
        
        celular: function(celular) {
            return /^[0-9]{10,15}$/.test(celular);
        },
        
        password: function(password) {
            return password.length >= 6;
        }
    };
    
    // Función para limpiar formularios
    window.clearForm = function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
        }
    };
    
    // Función para capitalizar texto
    window.capitalizeWords = function(str) {
        return str.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
    };
    
    // Auto-capitalización para campos de nombre
    document.querySelectorAll('input[name="nombres_completos"]').forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = capitalizeWords(this.value);
        });
    });
    
    // Solo números para campos de cédula y celular
    document.querySelectorAll('input[name="cedula"], input[name="celular"]').forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    // Minúsculas automáticas para emails
    document.querySelectorAll('input[type="email"]').forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.toLowerCase();
        });
    });
    
    // Efectos visuales para botones
    document.querySelectorAll('.btn').forEach(function(btn) {
        btn.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(1px)';
        });
        
        btn.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(0)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Mejorar navegación con teclado
    document.addEventListener('keydown', function(e) {
        // ESC para cerrar modales o volver
        if (e.key === 'Escape') {
            // Lógica para cerrar modales si existen
        }
        
        // Ctrl+Enter para enviar formularios
        if (e.ctrlKey && e.key === 'Enter') {
            const form = document.querySelector('form');
            if (form) {
                form.submit();
            }
        }
    });
    
    // Guardar datos del formulario en localStorage para recuperación
    function saveFormData(formId) {
        const form = document.getElementById(formId);
        if (form) {
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (key !== 'password') { // No guardar contraseñas
                    data[key] = value;
                }
            }
            
            localStorage.setItem(formId + '_backup', JSON.stringify(data));
        }
    }
    
    // Recuperar datos del formulario desde localStorage
    function restoreFormData(formId) {
        const savedData = localStorage.getItem(formId + '_backup');
        if (savedData) {
            const data = JSON.parse(savedData);
            const form = document.getElementById(formId);
            
            if (form) {
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.value = data[key];
                    }
                });
            }
        }
    }
    
    // Auto-guardar formularios cada 30 segundos
    const forms = document.querySelectorAll('form[id]');
    forms.forEach(function(form) {
        setInterval(function() {
            saveFormData(form.id);
        }, 30000);
        
        // Restaurar datos al cargar la página
        restoreFormData(form.id);
        
        // Limpiar datos guardados al enviar exitosamente
        form.addEventListener('submit', function() {
            setTimeout(function() {
                localStorage.removeItem(form.id + '_backup');
            }, 1000);
        });
    });
    
    // Función para mostrar/ocultar contraseñas
    window.togglePasswordVisibility = function(inputId) {
        const input = document.getElementById(inputId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    };
    
    // Agregar botones para mostrar/ocultar contraseñas
    document.querySelectorAll('input[type="password"]').forEach(function(input) {
        const container = input.parentNode;
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.innerHTML = '👁️';
        toggleBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
        `;
        
        container.style.position = 'relative';
        container.appendChild(toggleBtn);
        
        toggleBtn.addEventListener('click', function() {
            togglePasswordVisibility(input.id);
            this.innerHTML = input.type === 'password' ? '👁️' : '🙈';
        });
    });
    
    // Inicialización completada
    console.log('🎉 Sistema Luxury Lashes cargado correctamente');
});