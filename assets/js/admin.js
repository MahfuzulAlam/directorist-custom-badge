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
            this.loadBadgeData();
        },

        bindEvents: function() {
            var self = this;

            // Add badge button
            $(document).on('click', '.dcb-add-badge-btn', function() {
                self.showBadgeForm();
            });

            // Close/Cancel form
            $(document).on('click', '.dcb-close-form, .dcb-cancel-form', function() {
                self.hideBadgeForm();
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

        showBadgeForm: function() {
            this.resetForm();
            $('#dcb-badge-form-wrapper').slideDown();
            $('html, body').animate({
                scrollTop: $('#dcb-badge-form-wrapper').offset().top - 50
            }, 300);
        },

        hideBadgeForm: function() {
            $('#dcb-badge-form-wrapper').slideUp();
            this.resetForm();
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

        loadBadgeData: function() {
            // Load existing badges data if needed
            // This is handled server-side in the template
        },

        editBadge: function(badgeId) {
            var self = this;
            
            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dcb_get_badge',
                    id: badgeId,
                    nonce: dcbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.populateForm(response.data.badge);
                        self.showBadgeForm();
                    } else {
                        self.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showNotice(dcbAdmin.strings.error, 'error');
                }
            });
        },

        populateForm: function(badge) {
            this.currentBadge = badge;
            
            $('#dcb-badge-id').val(badge.id || '');
            $('#dcb-badge-order').val(badge.order || '');
            $('#dcb-badge-title').val(badge.badge_title || '');
            $('#dcb-badge-icon').val(badge.badge_icon || '');
            $('#dcb-badge-id-field').val(badge.badge_id || '');
            $('#dcb-badge-label').val(badge.badge_label || '');
            $('#dcb-badge-class').val(badge.badge_class || '');
            $('#dcb-condition-relation').val(badge.condition_relation || 'AND');
            $('#dcb-badge-active').prop('checked', badge.is_active !== false);

            // Populate conditions
            $('#dcb-conditions-list').empty();
            this.conditionIndex = 0;
            
            if (badge.conditions && badge.conditions.length > 0) {
                var self = this;
                badge.conditions.forEach(function(condition, idx) {
                    var template = $('#dcb-condition-template').html();
                    var index = self.conditionIndex++;
                    var html = template.replace(/\{\{index\}\}/g, index);
                    
                    $('#dcb-conditions-list').append(html);
                    
                    var $condition = $('#dcb-conditions-list .dcb-condition-item').last();
                    
                    $condition.find('.dcb-condition-type').val(condition.type);
                    self.handleConditionTypeChange($condition.find('.dcb-condition-type'));
                    
                    if (condition.type === 'meta') {
                        $condition.find('input[name="badge[conditions][' + index + '][meta_key]"]').val(condition.meta_key || '');
                        $condition.find('input[name="badge[conditions][' + index + '][meta_value]"]').val(condition.meta_value || '');
                        $condition.find('select[name="badge[conditions][' + index + '][compare]"]').val(condition.compare || '=');
                        $condition.find('select[name="badge[conditions][' + index + '][type_cast]"]').val(condition.type_cast || 'CHAR');
                    } else if (condition.type === 'pricing_plan') {
                        $condition.find('input[name="badge[conditions][' + index + '][plan_id]"]').val(condition.plan_id || '');
                        $condition.find('select[name="badge[conditions][' + index + '][compare]"]').val(condition.compare || '=');
                    }
                });
            }
        },

        saveBadge: function() {
            var self = this;
            var formData = $('#dcb-badge-form').serialize();
            var $saveBtn = $('.dcb-save-badge');
            var originalText = $saveBtn.text();

            // Validate required fields
            if (!this.validateForm()) {
                return;
            }

            $saveBtn.prop('disabled', true).text(dcbAdmin.strings.saving);

            $.ajax({
                url: dcbAdmin.ajaxUrl,
                type: 'POST',
                data: formData + '&action=dcb_save_badge&nonce=' + dcbAdmin.nonce,
                success: function(response) {
                    $saveBtn.prop('disabled', false).text(originalText);
                    
                    if (response.success) {
                        self.showNotice(response.data.message, 'success');
                        self.hideBadgeForm();
                        setTimeout(function() {
                            location.reload();
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

