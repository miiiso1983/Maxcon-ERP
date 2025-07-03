/**
 * Select2 Configuration and Initialization
 * Enhanced searchable dropdowns for MAXCON ERP
 */

// Configuration object for different select types
const select2Configs = {
    default: {
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 5,
        language: {
            noResults: function() {
                return 'لا توجد نتائج';
            },
            searching: function() {
                return 'جاري البحث...';
            },
            loadingMore: function() {
                return 'تحميل المزيد...';
            },
            inputTooShort: function(args) {
                return 'يرجى إدخال ' + (args.minimum - args.input.length) + ' أحرف أو أكثر';
            },
            inputTooLong: function(args) {
                return 'يرجى حذف ' + (args.input.length - args.maximum) + ' أحرف';
            },
            maximumSelected: function(args) {
                return 'يمكنك اختيار ' + args.maximum + ' عناصر فقط';
            }
        }
    },
    
    customer: {
        templateResult: function(option) {
            if (!option.id) return option.text;
            
            const $option = $('<span>')
                .append($('<i class="fas fa-user me-2 text-primary">'))
                .append(option.text);
            return $option;
        },
        templateSelection: function(option) {
            return option.text;
        },
        escapeMarkup: function(markup) {
            return markup;
        }
    },
    
    product: {
        templateResult: function(option) {
            if (!option.id) return option.text;
            
            const $option = $('<span>')
                .append($('<i class="fas fa-box me-2 text-success">'))
                .append(option.text);
            return $option;
        },
        templateSelection: function(option) {
            return option.text;
        },
        escapeMarkup: function(markup) {
            return markup;
        }
    },
    
    category: {
        templateResult: function(option) {
            if (!option.id) return option.text;
            
            const icons = {
                'medicines': 'fas fa-pills text-danger',
                'medical-devices': 'fas fa-stethoscope text-info',
                'supplements': 'fas fa-capsules text-warning',
                'equipment': 'fas fa-x-ray text-primary',
                'consumables': 'fas fa-syringe text-success',
                'laboratory': 'fas fa-microscope text-purple'
            };
            
            const iconClass = icons[option.id] || 'fas fa-tag text-secondary';
            const $option = $('<span>')
                .append($(`<i class="${iconClass} me-2">`))
                .append(option.text);
            return $option;
        },
        templateSelection: function(option) {
            return option.text;
        },
        escapeMarkup: function(markup) {
            return markup;
        }
    },
    
    payment: {
        templateResult: function(option) {
            if (!option.id) return option.text;
            
            const icons = {
                'cash': 'fas fa-money-bill-wave text-success',
                'card': 'fas fa-credit-card text-primary',
                'bank_transfer': 'fas fa-university text-info',
                'check': 'fas fa-file-invoice text-warning',
                'installment': 'fas fa-calendar-alt text-purple'
            };
            
            const iconClass = icons[option.id] || 'fas fa-dollar-sign text-secondary';
            const $option = $('<span>')
                .append($(`<i class="${iconClass} me-2">`))
                .append(option.text);
            return $option;
        },
        templateSelection: function(option) {
            return option.text;
        },
        escapeMarkup: function(markup) {
            return markup;
        }
    },
    
    status: {
        templateResult: function(option) {
            if (!option.id) return option.text;
            
            const statusColors = {
                'active': 'text-success',
                'inactive': 'text-secondary',
                'paid': 'text-success',
                'partial': 'text-warning',
                'pending': 'text-info',
                'overdue': 'text-danger',
                'in_stock': 'text-success',
                'low_stock': 'text-warning',
                'out_of_stock': 'text-danger'
            };
            
            const colorClass = statusColors[option.id] || 'text-secondary';
            const $option = $('<span>')
                .append($(`<i class="fas fa-circle ${colorClass} me-2">`))
                .append(option.text);
            return $option;
        },
        templateSelection: function(option) {
            return option.text;
        },
        escapeMarkup: function(markup) {
            return markup;
        }
    }
};

// Main initialization function
function initializeSelect2() {
    // Initialize all select elements that don't already have Select2
    $('select:not(.select2-hidden-accessible):not(.no-select2)').each(function() {
        const $select = $(this);
        
        // Skip if already initialized
        if ($select.data('select2')) return;
        
        // Determine configuration based on element attributes
        let config = { ...select2Configs.default };
        
        // Set placeholder
        const placeholder = $select.attr('data-placeholder') || 
                          $select.attr('placeholder') || 
                          $select.find('option:first').text() || 
                          'اختر خياراً';
        config.placeholder = placeholder;
        
        // Set allowClear based on required attribute
        config.allowClear = !$select.prop('required');
        
        // Determine search threshold
        const optionCount = $select.find('option').length;
        config.minimumResultsForSearch = optionCount > 5 ? 0 : Infinity;
        
        // Apply specific configurations based on element attributes
        const selectId = $select.attr('id') || '';
        const selectName = $select.attr('name') || '';
        const selectClass = $select.attr('class') || '';
        
        // Customer selects
        if (selectId.includes('customer') || selectName.includes('customer') || selectClass.includes('customer')) {
            Object.assign(config, select2Configs.customer);
        }
        
        // Product selects
        else if (selectId.includes('product') || selectName.includes('product') || selectClass.includes('product')) {
            Object.assign(config, select2Configs.product);
        }
        
        // Category selects
        else if (selectId.includes('category') || selectName.includes('category') || selectClass.includes('category')) {
            Object.assign(config, select2Configs.category);
        }
        
        // Payment method selects
        else if (selectId.includes('payment') || selectName.includes('payment')) {
            Object.assign(config, select2Configs.payment);
        }
        
        // Status selects
        else if (selectId.includes('status') || selectName.includes('status')) {
            Object.assign(config, select2Configs.status);
        }
        
        // Initialize Select2
        $select.select2(config);
        
        // Handle validation states
        if ($select.hasClass('is-invalid')) {
            $select.next('.select2-container').addClass('is-invalid');
        }
        if ($select.hasClass('is-valid')) {
            $select.next('.select2-container').addClass('is-valid');
        }
    });
}

// Function to refresh Select2 instances
function refreshSelect2() {
    $('.select2-hidden-accessible').select2('destroy');
    initializeSelect2();
}

// Function to add new option to select
function addOptionToSelect(selectId, value, text, selected = false) {
    const $select = $('#' + selectId);
    if ($select.length) {
        const newOption = new Option(text, value, selected, selected);
        $select.append(newOption);
        if (selected) {
            $select.trigger('change');
        }
    }
}

// Function to update select options
function updateSelectOptions(selectId, options) {
    const $select = $('#' + selectId);
    if ($select.length) {
        $select.empty();
        options.forEach(option => {
            const newOption = new Option(option.text, option.value, option.selected, option.selected);
            $select.append(newOption);
        });
        $select.trigger('change');
    }
}

// Function to enable/disable select
function toggleSelect(selectId, enabled = true) {
    const $select = $('#' + selectId);
    if ($select.length) {
        $select.prop('disabled', !enabled);
        if ($select.data('select2')) {
            $select.select2('destroy');
            initializeSelect2();
        }
    }
}

// Function to clear select
function clearSelect(selectId) {
    const $select = $('#' + selectId);
    if ($select.length) {
        $select.val(null).trigger('change');
    }
}

// Initialize when document is ready
$(document).ready(function() {
    initializeSelect2();
    
    // Re-initialize when new elements are added dynamically
    const observer = new MutationObserver(function(mutations) {
        let shouldReinit = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.tagName === 'SELECT' || $(node).find('select').length > 0) {
                            shouldReinit = true;
                        }
                    }
                });
            }
        });
        
        if (shouldReinit) {
            setTimeout(initializeSelect2, 100);
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// Make functions globally available
window.initializeSelect2 = initializeSelect2;
window.refreshSelect2 = refreshSelect2;
window.addOptionToSelect = addOptionToSelect;
window.updateSelectOptions = updateSelectOptions;
window.toggleSelect = toggleSelect;
window.clearSelect = clearSelect;
