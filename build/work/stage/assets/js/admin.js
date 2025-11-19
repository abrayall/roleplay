(function($) {
    'use strict';

    var Roleplay = {
        init: function() {
            this.bindEvents();
            this.loadRoles();
            this.loadCapabilities();
        },

        bindEvents: function() {
            $('#roleplay-add-role').on('click', this.openAddModal.bind(this));
            $('.roleplay-modal-close, #roleplay-modal-cancel').on('click', this.closeModal.bind(this));
            $('#roleplay-modal-save').on('click', this.saveRole.bind(this));
            $(document).on('click', '.roleplay-edit-role', this.openEditModal.bind(this));
            $(document).on('click', '.roleplay-delete-role', this.showDeleteDialog.bind(this));
            $('#roleplay-confirm-delete').on('click', this.confirmDelete.bind(this));
            $('#roleplay-cancel-delete').on('click', this.closeDeleteDialog.bind(this));

            // Close modal on backdrop click
            $('#roleplay-modal').on('click', function(e) {
                if ($(e.target).is('#roleplay-modal')) {
                    Roleplay.closeModal();
                }
            });

            // Close modal on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    Roleplay.closeModal();
                }
            });
        },

        deleteSlug: null,

        showDeleteDialog: function(e) {
            this.deleteSlug = $(e.currentTarget).data('slug');
            document.getElementById('roleplay-delete-dialog').showModal();
        },

        closeDeleteDialog: function() {
            document.getElementById('roleplay-delete-dialog').close();
            this.deleteSlug = null;
        },

        confirmDelete: function() {
            if (!this.deleteSlug) return;

            var slug = this.deleteSlug;
            this.closeDeleteDialog();

            $.ajax({
                url: roleplay.ajax_url,
                type: 'POST',
                data: {
                    action: 'roleplay_delete_role',
                    nonce: roleplay.nonce,
                    slug: slug
                },
                success: function(response) {
                    if (response.success) {
                        Roleplay.loadRoles();
                    } else {
                        alert(response.data || roleplay.strings.error);
                    }
                },
                error: function() {
                    alert(roleplay.strings.error);
                }
            });
        },

        loadRoles: function() {
            $.ajax({
                url: roleplay.ajax_url,
                type: 'POST',
                data: {
                    action: 'roleplay_get_roles',
                    nonce: roleplay.nonce
                },
                success: function(response) {
                    if (response.success) {
                        Roleplay.renderRoles(response.data);
                    }
                },
                error: function() {
                    alert(roleplay.strings.error);
                }
            });
        },

        renderRoles: function(roles) {
            var html = '';

            if (roles.length === 0) {
                html = '<tr><td colspan="3">No roles found.</td></tr>';
            } else {
                roles.forEach(function(role) {
                    html += '<tr>';
                    html += '<td class="column-name"><strong>' + Roleplay.escapeHtml(role.name) + '</strong></td>';
                    html += '<td class="column-slug"><span class="roleplay-slug">' + Roleplay.escapeHtml(role.slug) + '</span></td>';
                    html += '<td class="column-capabilities">' + role.capabilities_summary.replace(/\n/g, '<br>') + '</td>';
                    html += '<td class="column-actions">';
                    html += '<button type="button" class="button roleplay-edit-role" data-slug="' + Roleplay.escapeHtml(role.slug) + '">Edit</button>';
                    if (role.slug !== 'administrator') {
                        html += '<button type="button" class="button roleplay-delete-role" data-slug="' + Roleplay.escapeHtml(role.slug) + '">Delete</button>';
                    } else {
                        html += '<button type="button" class="button" disabled>Delete</button>';
                    }
                    html += '</td>';
                    html += '</tr>';
                });
            }

            $('#roleplay-roles-list').html(html);
            $('#roleplay-total').text('Total: ' + roles.length);

            // Render cards for mobile
            var cards = '';
            roles.forEach(function(role) {
                cards += '<div class="roleplay-card">';
                cards += '<div class="roleplay-card-header">';
                cards += '<h3 class="roleplay-card-title">' + Roleplay.escapeHtml(role.name) + '</h3>';
                cards += '<span class="roleplay-slug roleplay-card-slug">' + Roleplay.escapeHtml(role.slug) + '</span>';
                cards += '</div>';
                cards += '<div class="roleplay-card-caps">' + role.capabilities_summary + '</div>';
                cards += '<div class="roleplay-card-actions">';
                cards += '<button type="button" class="button roleplay-edit-role" data-slug="' + Roleplay.escapeHtml(role.slug) + '">Edit</button>';
                if (role.slug !== 'administrator') {
                    cards += '<button type="button" class="button roleplay-delete-role" data-slug="' + Roleplay.escapeHtml(role.slug) + '">Delete</button>';
                } else {
                    cards += '<button type="button" class="button" disabled>Delete</button>';
                }
                cards += '</div>';
                cards += '</div>';
            });
            $('#roleplay-cards').html(cards);
        },

        loadCapabilities: function() {
            $.ajax({
                url: roleplay.ajax_url,
                type: 'POST',
                data: {
                    action: 'roleplay_get_capabilities',
                    nonce: roleplay.nonce
                },
                success: function(response) {
                    if (response.success) {
                        Roleplay.capabilities = response.data;
                        Roleplay.renderCapabilities([]);
                    }
                }
            });
        },

        renderCapabilities: function(selected) {
            var html = '';

            if (this.capabilities) {
                this.capabilities.forEach(function(cap) {
                    var checked = selected.indexOf(cap) !== -1 ? 'checked' : '';
                    html += '<div class="roleplay-capability-item">';
                    html += '<label><input type="checkbox" name="capabilities[]" value="' + Roleplay.escapeHtml(cap) + '" ' + checked + '> ' + Roleplay.escapeHtml(cap) + '</label>';
                    html += '</div>';
                });
            }

            $('#roleplay-capabilities-list').html(html);
        },

        openAddModal: function() {
            $('#roleplay-modal-title').text('Add Role');
            $('#roleplay-role-slug').val('');
            $('#roleplay-role-name').val('');
            this.renderCapabilities([]);
            $('#roleplay-modal').show();
            $('#roleplay-role-name').focus();
        },

        openEditModal: function(e) {
            var slug = $(e.currentTarget).data('slug');

            $.ajax({
                url: roleplay.ajax_url,
                type: 'POST',
                data: {
                    action: 'roleplay_get_role',
                    nonce: roleplay.nonce,
                    slug: slug
                },
                success: function(response) {
                    if (response.success) {
                        var role = response.data;
                        $('#roleplay-modal-title').text('Edit Role');
                        $('#roleplay-role-slug').val(role.slug);
                        $('#roleplay-role-name').val(role.name);
                        Roleplay.renderCapabilities(role.capabilities);
                        $('#roleplay-modal').show();
                        $('#roleplay-role-name').focus();
                    }
                },
                error: function() {
                    alert(roleplay.strings.error);
                }
            });
        },

        closeModal: function() {
            $('#roleplay-modal').hide();
        },

        saveRole: function() {
            var slug = $('#roleplay-role-slug').val();
            var name = $('#roleplay-role-name').val().trim();

            if (!name) {
                alert('Please enter a role name.');
                return;
            }

            var capabilities = [];
            $('input[name="capabilities[]"]:checked').each(function() {
                capabilities.push($(this).val());
            });

            $('#roleplay-modal-save').prop('disabled', true);

            $.ajax({
                url: roleplay.ajax_url,
                type: 'POST',
                data: {
                    action: 'roleplay_save_role',
                    nonce: roleplay.nonce,
                    slug: slug,
                    name: name,
                    capabilities: capabilities
                },
                success: function(response) {
                    if (response.success) {
                        Roleplay.closeModal();
                        Roleplay.loadRoles();
                    } else {
                        alert(response.data || roleplay.strings.error);
                    }
                },
                error: function() {
                    alert(roleplay.strings.error);
                },
                complete: function() {
                    $('#roleplay-modal-save').prop('disabled', false);
                }
            });
        },


        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
    };

    $(document).ready(function() {
        Roleplay.init();
    });

})(jQuery);
