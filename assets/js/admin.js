/**
 * Directorist Custom Badges Admin JavaScript
 */

(function($) {
    'use strict';

    var DCBAdmin = {
        conditionIndex: 0,
        currentBadge: null,

        init: function() {
            this.bindEvents();
            this.initSortable();
            this.initColorPicker();
            this.loadBadgeData();
        },

        bindEvents: function() {
            var self = this;

            // Add badge button - redirect to form page
            $(document).on('click', '.dcb-add-badge-btn', function() {
                window.location.href = self.getFormUrl();
            });

            // Close/Cancel form - redirect to list page
            $(document).on('click', '.dcb-close-form, .dcb-cancel-form', function() {
                window.location.href = self.getListUrl();
            });

            // Save badge
            $(document).on('submit', '#dcb-badge-form', function(e) {
                e.preventDefault();
                self.saveBadge();
            });

            // Edit badge
            $(document).on('click', '.dcb-edit-badge', function() {
                var badgeId = $(this).data('badge-id');
                self.editBadge(badgeId);
            });

            // Delete badge
            $(document).on('click', '.dcb-delete-badge', function() {
                var badgeId = $(this).data('badge-id');
                self.deleteBadge(badgeId);
            });

            // Duplicate badge
            $(document).on('click', '.dcb-duplicate-badge', function() {
                var badgeId = $(this).data('badge-id');
                self.duplicateBadge(badgeId);
            });

            // Toggle active status
            $(document).on('change', '.dcb-toggle-active', function() {
                var badgeId = $(this).data('badge-id');
                self.toggleBadge(badgeId);
            });

            // Add condition
            $(document).on('click', '.dcb-add-condition', function() {
                self.addCondition();
            });

            // Remove condition
            $(document).on('click', '.dcb-remove-condition', function() {
                $(this).closest('.dcb-condition-item').remove();
            });

            // Condition type change
            $(document).on('change', '.dcb-condition-type', function() {
                self.handleConditionTypeChange($(this));
            });

            // Plan status condition change
            // Note: Fields remain visible, no special handling needed
            // $(document).on('change', '.dcb-plan-status-condition', function() {
            //     self.handlePlanStatusConditionChange($(this));
            // });

            // Badge ID validation
            $(document).on('blur', '#dcb-badge-id-field', function() {
                self.validateBadgeId($(this).val());
            });

            // Export badges
            $(document).on('click', '.dcb-export-badges', function() {
                self.exportBadges();
            });

            // Import badges
            $(document).on('change', '#dcb-import-file', function() {
                self.importBadges(this);
            });
        },

        initSortable: function() {
            var self = this;
            
            $('.dcb-badges-list').sortable({
                handle: '.dcb-drag-handle',
                axis: 'y',
                opacity: 0.6,
                cursor: 'move',
                update: function(event, ui) {
                    self.reorderBadges();
                }
            });
        },

        initColorPicker: function() {
            // Initialize WordPress color picker if available
            if (typeof jQuery.fn.wpColorPicker !== 'undefined') {
                $('.dcb-color-picker').wpColorPicker({
                    change: function(event, ui) {
                        // Color changed
                    },
                    clear: function() {
                        // Color cleared
                    }
                });
            }
        },

        getFormUrl: function(badgeId) {
            var url = dcbAdmin.ajaxUrl.replace('admin-ajax.php', 'admin.php');
            url += '?page=directorist-custom-badges-form';
            if (badgeId) {
                url += '&badge_id=' + encodeURIComponent(badgeId);
            }
            return url;
        },

        getListUrl: function() {
            var url = dcbAdmin.ajaxUrl.replace('admin-ajax.php', 'admin.php');
            return url + '?page=directorist-custom-badges';
        },

        resetForm: function() {
            $('#dcb-badge-form')[0].reset();
            $('#dcb-badge-id').val('');
            $('#dcb-badge-order').val('');
            $('#dcb-conditions-list').empty();
            this.conditionIndex = 0;
            this.currentBadge = null;
            $('.dcb-field-error').text('');
            $('.dcb-input, .dcb-select').removeClass('dcb-error');
        },

        addCondition: function() {
            var template = $('#dcb-condition-template').html();
            var index = this.conditionIndex++;
            var html = template.replace(/\{\{index\}\}/g, index);
            
            $('#dcb-conditions-list').append(html);
            
            // Initialize condition type
            var $condition = $('#dcb-conditions-list .dcb-condition-item').last();
            this.handleConditionTypeChange($condition.find('.dcb-condition-type'));
        },

        handleConditionTypeChange: function($select) {
            var type = $select.val();
            var $condition = $select.closest('.dcb-condition-item');
            var $metaFields = $condition.find('.dcb-meta-fields');
            var $planFields = $condition.find('.dcb-pricing-plan-fields');

            if (type === 'meta') {
                $metaFields.show();
                $planFields.hide();
            } else if (type === 'pricing_plan') {
                $metaFields.hide();
                $planFields.show();
            }
        },

        handlePlanStatusConditionChange: function($select) {
            // Function kept for potential future use, but no longer hides fields
            // Plan ID and Compare fields remain visible regardless of plan_status_condition selection
        },

        loadBadgeData: function() {
            // Load existing badges data if needed
            // This is handled server-side in the template
        },

        editBadge: function(badgeId) {
            // Redirect to form page with badge ID
            window.location.href = this.getFormUrl(badgeId);
        },

        populateForm: function(badge) {
            this.currentBadge = badge;
            
            // Populate basic fields
            $('#dcb-badge-id').val(badge.id || '');
            $('#dcb-badge-order').val(badge.order || '');
            $('#dcb-badge-title').val(badge.badge_title || '');
            $('#dcb-badge-icon').val(badge.badge_icon || '');
            $('#dcb-badge-id-field').val(badge.badge_id || '');
            $('#dcb-badge-label').val(badge.badge_label || '');
            $('#dcb-badge-class').val(badge.badge_class || '');
            $('#dcb-badge-color').val(badge.badge_color || '');
            // Update color picker if it exists
            if (typeof jQuery.fn.wpColorPicker !== 'undefined' && $('#dcb-badge-color').hasClass('wp-color-picker')) {
                $('#dcb-badge-color').wpColorPicker('color', badge.badge_color || '');
            }
            $('#dcb-condition-relation').val(badge.condition_relation || 'AND');
            $('#dcb-badge-active').prop('checked', badge.is_active === true || badge.is_active === '1' || badge.is_active === 1);

            // Populate conditions
            $('#dcb-conditions-list').empty();
            this.conditionIndex = 0;
            
            if (badge.conditions && badge.conditions.length > 0) {
                var self = this;
                badge.conditions.forEach(function(condition, idx) {
                    // Add condition using the addCondition method to ensure proper structure
                    self.addCondition();
                    
                    // Get the last added condition
                    var $condition = $('#dcb-conditions-list .dcb-condition-item').last();
                    var index = self.conditionIndex - 1; // Get the index that was just used
                    
                    // Set condition type first
                    var $conditionType = $condition.find('.dcb-condition-type');
                    $conditionType.val(condition.type || 'meta');
                    
                    // Trigger change to show/hide appropriate fields
                    $conditionType.trigger('change');
                    
                    // Use setTimeout to ensure fields are visible before setting values
                    setTimeout(function() {
                        if (condition.type === 'meta') {
                            // Set meta condition values - use more specific selectors
                            var $metaFields = $condition.find('.dcb-meta-fields');
                            $metaFields.find('input[name="badge[conditions][' + index + '][meta_key]"]').val(condition.meta_key || '');
                            $metaFields.find('input[name="badge[conditions][' + index + '][meta_value]"]').val(condition.meta_value || '');
                            $metaFields.find('select[name="badge[conditions][' + index + '][compare]"]').val(condition.compare || '=');
                            $metaFields.find('select[name="badge[conditions][' + index + '][type_cast]"]').val(condition.type_cast || 'CHAR');
                        } else if (condition.type === 'pricing_plan') {
                            // Set pricing plan condition values - use more specific selectors
                            var $planFields = $condition.find('.dcb-pricing-plan-fields');
                            $planFields.find('select[name="badge[conditions][' + index + '][plan_status_condition]"]').val(condition.plan_status_condition || '');
                            $planFields.find('input[name="badge[conditions][' + index + '][plan_id]"]').val(condition.plan_id || '');
                            $planFields.find('select[name="badge[conditions][' + index + '][compare]"]').val(condition.compare || '=');
                        }
                    }, 50);
                });
            }
        },

        saveBadge: function() {
            var self = this;
            var $saveBtn = $('.dcb-save-badge');
            var originalText = $saveBtn.text();

            // Validate required fields
            if (!this.validateForm()) {
                return;
            }

            // Build form data object manually to ensure proper nesting
            var formData = {
                badge: {
                    id: $('#dcb-badge-id').val() || '',
                    order: $('#dcb-badge-order').val() || '',
                    badge_title: $('#dcb-badge-title').val() || '',
                    badge_icon: $('#dcb-badge-icon').val() || '',
                    badge_id: $('#dcb-badge-id-field').val() || '',
                    badge_label: $('#dcb-badge-label').val() || '',
                    badge_class: $('#dcb-badge-class').val() || '',
                    badge_color: $('#dcb-badge-color').val() || '',
                    condition_relation: $('#dcb-condition-relation').val() || 'AND',
                    is_active: $('#dcb-badge-active').is(':checked') ? 1 : 0,
                    conditions: []
                },
                action: 'dcb_save_badge',
                nonce: dcbAdmin.nonce
            };

            // Collect conditions - loop through all condition items
            $('#dcb-conditions-list .dcb-condition-item').each(function() {
                var $condition = $(this);
                var conditionType = $condition.find('.dcb-condition-type').val();
                
                if (!conditionType) {
                    return; // Skip if no type selected
                }

                var condition = {
                    type: conditionType
                };

                if (conditionType === 'meta') {
                    var $metaFields = $condition.find('.dcb-meta-fields');
                    if ($metaFields.length) {
                        condition.meta_key = $metaFields.find('input[name*="[meta_key]"]').val() || '';
                        condition.meta_value = $metaFields.find('input[name*="[meta_value]"]').val() || '';
                        condition.compare = $metaFields.find('select[name*="[compare]"]').val() || '=';
                        condition.type_cast = $metaFields.find('select[name*="[type_cast]"]').val() || 'CHAR';
                    } else {
                        // Fallback: try direct selectors if meta-fields wrapper not found
                        condition.meta_key = $condition.find('input[name*="[meta_key]"]').val() || '';
                        condition.meta_value = $condition.find('input[name*="[meta_value]"]').val() || '';
                        condition.compare = $condition.find('select[name*="[compare]"]').not('.dcb-pricing-plan-fields select').val() || '=';
                        condition.type_cast = $condition.find('select[name*="[type_cast]"]').val() || 'CHAR';
                    }
                } else if (conditionType === 'pricing_plan') {
                    var $planFields = $condition.find('.dcb-pricing-plan-fields');
                    if ($planFields.length) {
                        condition.plan_status_condition = $planFields.find('select[name*="[plan_status_condition]"]').val() || '';
                        condition.plan_id = $planFields.find('input[name*="[plan_id]"]').val() || '';
                        condition.compare = $planFields.find('select[name*="[compare]"]').val() || '=';
                    } else {
                        // Fallback: try direct selectors if pricing-plan-fields wrapper not found
                        condition.plan_status_condition = $condition.find('select[name*="[plan_status_condition]"]').val() || '';
                        condition.plan_id = $condition.find('input[name*="[plan_id]"]').val() || '';
                        condition.compare = $condition.find('.dcb-pricing-plan-fields select[name*="[compare]"]').val() || '=';
                    }
                }

                // Add condition - validation happens server-side
                // EXISTS and NOT EXISTS don't require meta_key, so we add all conditions
                formData.badge.conditions.push(condition);
            });

            $saveBtn.prop('disabled', true).text(dcbAdmin.strings.saving);

            // Convert to format WordPress expects (nested arrays)
            var postData = {
                action: 'dcb_save_badge',
                nonce: dcbAdmin.nonce
            };

            // Add badge data with proper nesting
            postData['badge[id]'] = formData.badge.id;
            postData['badge[order]'] = formData.badge.order;
            postData['badge[badge_title]'] = formData.badge.badge_title;
            postData['badge[badge_icon]'] = formData.badge.badge_icon;
            postData['badge[badge_id]'] = formData.badge.badge_id;
            postData['badge[badge_label]'] = formData.badge.badge_label;
            postData['badge[badge_class]'] = formData.badge.badge_class;
            postData['badge[badge_color]'] = formData.badge.badge_color;
            postData['badge[condition_relation]'] = formData.badge.condition_relation;
            postData['badge[is_active]'] = formData.badge.is_active;

            // Add conditions with proper array indexing
            formData.badge.conditions.forEach(function(condition, index) {
                postData['badge[conditions][' + index + '][type]'] = condition.type;
                
                if (condition.type === 'meta') {
                    postData['badge[conditions][' + index + '][meta_key]'] = condition.meta_key || '';
                    postData['badge[conditions][' + index + '][meta_value]'] = condition.meta_value || '';
                    postData['badge[conditions][' + index + '][compare]'] = condition.compare || '=';
                    postData['badge[conditions][' + index + '][type_cast]'] = condition.type_cast || 'CHAR';
                } else if (condition.type === 'pricing_plan') {
                    postData['badge[conditions][' + index + '][plan_status_condition]'] = condition.plan_status_condition || '';
                    postData['badge[conditions][' + index + '][plan_id]'] = condition.plan_id || '';
                    postData['badge[conditions][' + index + '][compare]'] = condition.compare || '=';
                }
            });

            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: postData,
                success: function(response) {
                    $saveBtn.prop('disabled', false).text(originalText);
                    
                    if (response.success) {
                        self.showNotice(response.data.message, 'success');
                        setTimeout(function() {
                            window.location.href = self.getListUrl();
                        }, 1000);
                    } else {
                        self.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    $saveBtn.prop('disabled', false).text(originalText);
                    self.showNotice(dcbAdmin.strings.error, 'error');
                }
            });
        },

        validateForm: function() {
            var isValid = true;
            var $title = $('#dcb-badge-title');
            var $badgeId = $('#dcb-badge-id-field');
            var $label = $('#dcb-badge-label');

            // Reset errors
            $('.dcb-field-error').text('');

            // Validate title
            if (!$title.val().trim()) {
                isValid = false;
                $title.addClass('dcb-error');
            } else {
                $title.removeClass('dcb-error');
            }

            // Validate badge ID
            var badgeId = $badgeId.val().trim();
            if (!badgeId) {
                isValid = false;
                $badgeId.addClass('dcb-error');
                $badgeId.siblings('.dcb-field-error').text(dcbAdmin.strings.requiredField);
            } else if (!/^[a-z0-9-]+$/.test(badgeId)) {
                isValid = false;
                $badgeId.addClass('dcb-error');
                $badgeId.siblings('.dcb-field-error').text(dcbAdmin.strings.invalidBadgeId);
            } else {
                $badgeId.removeClass('dcb-error');
            }

            // Validate label
            if (!$label.val().trim()) {
                isValid = false;
                $label.addClass('dcb-error');
            } else {
                $label.removeClass('dcb-error');
            }

            if (!isValid) {
                this.showNotice(dcbAdmin.strings.requiredField, 'error');
            }

            return isValid;
        },

        validateBadgeId: function(badgeId) {
            var self = this;
            var $field = $('#dcb-badge-id-field');
            var $error = $field.siblings('.dcb-field-error');
            var currentId = $('#dcb-badge-id').val();

            if (!badgeId || !/^[a-z0-9-]+$/.test(badgeId)) {
                $error.text(dcbAdmin.strings.invalidBadgeId);
                $field.addClass('dcb-error');
                return;
            }

            // Check uniqueness (only if not editing same badge)
            // This would require an AJAX call to check, but for now we'll validate on save
            $error.text('');
            $field.removeClass('dcb-error');
        },

        deleteBadge: function(badgeId) {
            var self = this;
            
            if (!confirm(dcbAdmin.strings.confirmDelete)) {
                return;
            }

            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dcb_delete_badge',
                    id: badgeId,
                    nonce: dcbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice(response.data.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        self.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showNotice(dcbAdmin.strings.error, 'error');
                }
            });
        },

        duplicateBadge: function(badgeId) {
            var self = this;
            
            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dcb_duplicate_badge',
                    id: badgeId,
                    nonce: dcbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice(response.data.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        self.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showNotice(dcbAdmin.strings.error, 'error');
                }
            });
        },

        toggleBadge: function(badgeId) {
            var self = this;
            
            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dcb_toggle_badge',
                    id: badgeId,
                    nonce: dcbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Status updated, no need to reload
                    } else {
                        self.showNotice(response.data.message, 'error');
                        // Revert toggle
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    }
                },
                error: function() {
                    self.showNotice(dcbAdmin.strings.error, 'error');
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                }
            });
        },

        reorderBadges: function() {
            var self = this;
            var order = [];
            
            $('.dcb-badge-row').each(function() {
                order.push($(this).data('badge-id'));
            });

            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dcb_reorder_badges',
                    order: order,
                    nonce: dcbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Successfully reordered
                    } else {
                        self.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showNotice(dcbAdmin.strings.error, 'error');
                }
            });
        },

        exportBadges: function() {
            var self = this;
            
            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dcb_export_badges',
                    nonce: dcbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var dataStr = JSON.stringify(response.data.badges, null, 2);
                        var dataBlob = new Blob([dataStr], {type: 'application/json'});
                        var url = URL.createObjectURL(dataBlob);
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = 'directorist-custom-badges-' + new Date().getTime() + '.json';
                        link.click();
                        URL.revokeObjectURL(url);
                    } else {
                        self.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showNotice(dcbAdmin.strings.error, 'error');
                }
            });
        },

        importBadges: function(input) {
            var self = this;
            var file = input.files[0];
            
            if (!file) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var badges = JSON.parse(e.target.result);
                    
                    if (!Array.isArray(badges)) {
                        self.showNotice('Invalid file format.', 'error');
                        return;
                    }

                    if (!confirm('Import ' + badges.length + ' badge(s)?')) {
                        return;
                    }

                    $.ajax({
                        url: dcbAdmin.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'dcb_import_badges',
                            badges: badges,
                            nonce: dcbAdmin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                self.showNotice(response.data.message, 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                self.showNotice(response.data.message, 'error');
                            }
                        },
                        error: function() {
                            self.showNotice(dcbAdmin.strings.error, 'error');
                        }
                    });
                } catch (error) {
                    self.showNotice('Error parsing JSON file.', 'error');
                }
            };
            reader.readAsText(file);
        },

        showNotice: function(message, type) {
            type = type || 'info';
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.dcb-notices').html($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        DCBAdmin.init();
    });

})(jQuery);

