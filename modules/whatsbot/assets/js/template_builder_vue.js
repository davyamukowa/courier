(function ($, Vue) {
    "use strict";

    if (typeof Vue === "undefined") {
        return;
    }

    var config = window.wbTemplateBuilderConfig || {};

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function createUid() {
        return "wb-" + Date.now() + "-" + Math.random().toString(36).slice(2, 8);
    }

    function normalizeType(value) {
        value = String(value || "QUICK_REPLY").toUpperCase();
        if (["QUICK_REPLY", "URL", "PHONE_NUMBER"].indexOf(value) === -1) {
            return "QUICK_REPLY";
        }
        return value;
    }

    function countBodyVariables(text) {
        var nums = [];
        String(text || "").replace(/{{\s*(\d+)\s*}}/g, function (_, num) {
            num = parseInt(num, 10);
            if (nums.indexOf(num) === -1) {
                nums.push(num);
            }
        });
        return nums;
    }

    function buildPreviewBodyHtml(text, bodyVariables) {
        var raw = String(text || "").replace(/\r\n/g, "\n");
        var variableValues = {};
        var variableTokens = [];

        (bodyVariables || []).forEach(function (row) {
            variableValues[parseInt(row.index, 10)] = row.value || "";
        });

        raw = raw.replace(/{{\s*(\d+)\s*}}/g, function (match, num) {
            num = parseInt(num, 10);
            var token = "@@WBVAR" + variableTokens.length + "@@";
            variableTokens.push({
                token: token,
                html: escapeHtml(variableValues[num] || match),
            });
            return token;
        });

        function restoreTokens(segment) {
            variableTokens.forEach(function (tokenInfo) {
                segment = segment.replace(new RegExp(tokenInfo.token, "g"), tokenInfo.html);
            });
            return segment;
        }

        function formatTextSegment(segment) {
            segment = escapeHtml(segment);
            segment = segment
                .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
                .replace(/_(.+?)_/g, "<em>$1</em>")
                .replace(/~(.+?)~/g, "<del>$1</del>");
            segment = segment.replace(/\n/g, "<br>");
            return restoreTokens(segment);
        }

        return raw
            .split(/(```[\s\S]*?```)/g)
            .map(function (segment) {
                if (/^```[\s\S]*```$/.test(segment)) {
                    var code = segment.slice(3, -3);
                    code = restoreTokens(escapeHtml(code)).replace(/\n/g, "<br>");
                    return '<pre class="wb-preview-code">' + code + "</pre>";
                }

                return formatTextSegment(segment);
            })
            .join("");
    }

    function isValidHttpUrl(value) {
        try {
            var parsed = new URL(String(value || ""));
            return parsed.protocol === "http:" || parsed.protocol === "https:";
        } catch (e) {
            return false;
        }
    }

    new Vue({
        el: "#template_builder_app",
        data: function () {
            var buttons = (config.buttons || []).map(function (button) {
                return {
                    uid: createUid(),
                    type: normalizeType(button.type),
                    text: button.text || "",
                    url: button.url || "",
                    phone_number: button.phone_number || "",
                };
            });
            var buttonErrors = buttons.map(function () {
                return {};
            });

            return {
                templateName: $("#template_name").val() || "",
                headerFormat: config.headerFormat || "NONE",
                headerDataText: config.headerDataText || "",
                headerVariable1: config.headerVariable1 || "",
                headerMediaUrl: config.headerMediaUrl || "",
                headerMediaFile: null,
                headerMediaPreviewUrl: config.headerMediaPreviewUrl || "",
                bodyText: config.bodyText || "",
                bodyVariables: (config.bodyVariableIndexes || []).map(function (index) {
                    return {
                        index: parseInt(index, 10),
                        value: "",
                    };
                }),
                footerData: config.footerData || "",
                buttons: buttons,
                buttonErrors: buttonErrors,
                validationErrors: {},
                headerMaxLength: 60,
                footerMaxLength: 60,
                bodyMaxLength: 1024,
                caret: {
                    header: { start: 0, end: 0 },
                    body: { start: 0, end: 0 },
                },
                syncingBody: false,
                mediaObjectUrl: null,
            };
        },
        computed: {
            isMediaHeader: function () {
                return ["IMAGE", "VIDEO", "DOCUMENT"].indexOf(this.headerFormat) !== -1;
            },
            headerHasVariable: function () {
                return /{{\s*1\s*}}/.test(this.headerDataText || "");
            },
            headerCharacterCount: function () {
                return String(this.headerDataText || "").length;
            },
            footerCharacterCount: function () {
                return String(this.footerData || "").length;
            },
            mediaAccept: function () {
                if (this.headerFormat === "IMAGE") {
                    return "image/*";
                }
                if (this.headerFormat === "VIDEO") {
                    return "video/*";
                }
                if (this.headerFormat === "DOCUMENT") {
                    return ".pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx";
                }
                return "";
            },
            previewHeaderHtml: function () {
                if (this.headerFormat !== "TEXT") {
                    return "";
                }

                var example = this.headerVariable1 || "{{1}}";
                return escapeHtml(this.headerDataText || "").replace(/{{\s*1\s*}}/g, '<span class="tw-font-bold tw-text-neutral-900">' + escapeHtml(example) + "</span>");
            },
            previewMediaHtml: function () {
                if (!this.isMediaHeader) {
                    return "";
                }

                var previewUrl = this.headerMediaPreviewUrl || this.headerMediaUrl;
                if (!previewUrl) {
                    return "";
                }

                if (this.headerFormat === "IMAGE") {
                    return '<img src="' + escapeHtml(previewUrl) + '" alt="" style="max-width:100%;border-radius:6px;" onerror="this.style.display=\'none\'">';
                }
                if (this.headerFormat === "VIDEO") {
                    return '<video controls src="' + escapeHtml(previewUrl) + '" style="max-width:100%;border-radius:6px;"></video>';
                }
                return '<a href="' + escapeHtml(previewUrl) + '" target="_blank" class="wb-doc-preview"><i class="fa fa-file-text-o"></i><span>Document</span></a>';
            },
            previewBodyText: function () {
                var text = this.bodyText || "";
                return text.replace(/{{\s*(\d+)\s*}}/g, function (match, num) {
                    num = parseInt(num, 10);
                    var row = this.bodyVariables.find(function (item) {
                        return parseInt(item.index, 10) === num;
                    });
                    return row && row.value ? row.value : match;
                }.bind(this));
            },
            previewBodyHtml: function () {
                return buildPreviewBodyHtml(this.bodyText || "", this.bodyVariables || []);
            },
            bodyCharacterCount: function () {
                return String(this.bodyText || "").length;
            },
            hasButtons: function () {
                return this.buttons && this.buttons.length > 0;
            },
        },
        mounted: function () {
            var self = this;
            $("#template_builder_form").on("submit", function (event) {
                event.preventDefault();
                return false;
            });
            $("#header_data_format").on("change changed.bs.select", function () {
                self.headerFormat = String($(this).val() || "NONE").toUpperCase();
                self.handleHeaderFormatChange();
            });
            $("#category").on("change changed.bs.select", function () {
                self.clearValidationError("category");
            });
            $("#language").on("change changed.bs.select", function () {
                self.clearValidationError("language");
            });

            self.headerFormat = String($("#header_data_format").val() || self.headerFormat || "NONE").toUpperCase();

            if (this.headerFormat === "TEXT") {
                this.syncHeaderVariableVisibility();
            }

            this.syncBodyVariablesFromText();
            this.handleHeaderFormatChange();

            this.$nextTick(function () {
                self.refreshSelectpickers();
            });
        },
        beforeDestroy: function () {
            this.clearMediaPreviewObjectUrl();
        },
        watch: {
            headerMediaUrl: function () {
                if (this.isMediaHeader && !this.headerMediaFile) {
                    this.headerMediaPreviewUrl = this.headerMediaUrl;
                }
            },
            headerDataText: function () {
                this.syncHeaderVariableVisibility();
            },
        },
        methods: {
            refreshSelectpickers: function () {
                if (typeof init_selectpicker === "function") {
                    init_selectpicker();
                }
                if ($.fn.selectpicker) {
                    $(".selectpicker").selectpicker("refresh");
                }
            },
            syncHeaderVariableVisibility: function () {
                if (!this.headerHasVariable) {
                    this.headerVariable1 = "";
                }
            },
            handleHeaderFormatChange: function () {
                this.headerFormat = String(this.headerFormat || "NONE").toUpperCase();
                if (!this.headerFormat || ["NONE", "TEXT", "IMAGE", "VIDEO", "DOCUMENT"].indexOf(this.headerFormat) === -1) {
                    this.headerFormat = "NONE";
                }

                if (!this.isMediaHeader) {
                    this.headerMediaUrl = this.headerMediaUrl || "";
                    this.headerMediaFile = null;
                    this.clearMediaPreviewObjectUrl();
                    this.headerMediaPreviewUrl = "";
                }

                this.$nextTick(this.refreshSelectpickers);
            },
            rememberCaret: function (type, event) {
                var target = event && event.target ? event.target : null;
                if (!target) {
                    return;
                }

                this.caret[type] = {
                    start: target.selectionStart || 0,
                    end: target.selectionEnd || 0,
                };
            },
            insertAtCursor: function (type, text, refName, modelKey) {
                var el = this.$refs[refName];
                if (!el) {
                    return;
                }

                var caret = this.caret[type] || { start: 0, end: 0 };
                var current = this[modelKey] || "";
                var start = typeof caret.start === "number" ? caret.start : 0;
                var end = typeof caret.end === "number" ? caret.end : start;
                var next = current.substring(0, start) + text + current.substring(end);

                if (modelKey === "bodyText" && next.length > this.bodyMaxLength) {
                    alert_float("warning", "Message body cannot exceed 1024 characters.");
                    return;
                }

                this[modelKey] = next;

                this.$nextTick(function () {
                    el.focus();
                    if (typeof el.selectionStart === "number") {
                        el.selectionStart = el.selectionEnd = start + text.length;
                    }
                });
            },
            addHeaderVariable: function () {
                if (!/{{\s*1\s*}}/.test(this.headerDataText || "")) {
                    this.insertAtCursor("header", "{{1}}", "headerText", "headerDataText");
                }
            },
            removeHeaderVariable: function () {
                this.headerDataText = (this.headerDataText || "").replace(/{{\s*1\s*}}/g, "");
                this.headerVariable1 = "";
                this.syncHeaderVariableVisibility();
            },
            onHeaderTextInput: function () {
                this.clearValidationError("header_data_text");
                if (String(this.headerDataText || "").length > this.headerMaxLength) {
                    this.headerDataText = String(this.headerDataText || "").slice(0, this.headerMaxLength);
                }
                this.syncHeaderVariableVisibility();
            },
            onFooterInput: function () {
                this.clearValidationError("footer_data");
                if (String(this.footerData || "").length > this.footerMaxLength) {
                    this.footerData = String(this.footerData || "").slice(0, this.footerMaxLength);
                }
            },
            addBodyVariable: function () {
                var nextIndex = this.bodyVariables.length + 1;
                this.insertAtCursor("body", "{{" + nextIndex + "}}", "bodyText", "bodyText");
                this.syncBodyVariablesFromText();
            },
            removeBodyVariable: function (index) {
                this.bodyText = (this.bodyText || "").replace(new RegExp("{{\\s*" + index + "\\s*}}", "g"), "");
                this.syncBodyVariablesFromText();
            },
            onBodyTextInput: function () {
                this.clearValidationError("body_data");
                if (String(this.bodyText || "").length > this.bodyMaxLength) {
                    this.bodyText = String(this.bodyText || "").slice(0, this.bodyMaxLength);
                }
                this.syncBodyVariablesFromText();
            },
            formatBodyText: function (formatType) {
                var markers = {
                    bold: { open: "**", close: "**", cursorOffset: 2 },
                    italic: { open: "_", close: "_", cursorOffset: 1 },
                    strike: { open: "~", close: "~", cursorOffset: 1 },
                    code: { open: "```", close: "```", cursorOffset: 4, noSelectionNewLines: true },
                };
                var marker = markers[formatType];
                var el = this.$refs.bodyText;
                var current = String(this.bodyText || "");
                var caret = this.caret.body || { start: current.length, end: current.length };
                var start = typeof caret.start === "number" ? caret.start : current.length;
                var end = typeof caret.end === "number" ? caret.end : start;
                var selected = current.substring(start, end);
                var insertion = "";

                if (!marker) {
                    return;
                }

                if (start !== end) {
                    insertion = marker.open + selected + marker.close;
                } else if (marker.noSelectionNewLines) {
                    insertion = marker.open + "\n\n" + marker.close;
                } else {
                    insertion = marker.open + marker.close;
                }

                var next = current.substring(0, start) + insertion + current.substring(end);
                if (next.length > this.bodyMaxLength) {
                    alert_float("warning", "Message body cannot exceed 1024 characters.");
                    return;
                }

                this.bodyText = next;
                this.clearValidationError("body_data");
                this.syncBodyVariablesFromText();

                this.$nextTick(function () {
                    if (!el) {
                        return;
                    }

                    el.focus();
                    if (typeof el.selectionStart === "number") {
                        var cursorPosition;
                        if (start !== end) {
                            cursorPosition = start + insertion.length;
                        } else if (marker.noSelectionNewLines) {
                            cursorPosition = start + marker.open.length + 1;
                        } else {
                            cursorPosition = start + marker.open.length;
                        }
                        el.selectionStart = el.selectionEnd = cursorPosition;
                    }
                });
            },
            syncBodyVariablesFromText: function () {
                if (this.syncingBody) {
                    return;
                }

                this.syncingBody = true;

                var text = this.bodyText || "";
                var currentValues = {};
                this.bodyVariables.forEach(function (row) {
                    currentValues[row.index] = row.value || "";
                });

                var seen = {};
                var originalOrder = [];
                var normalized = text.replace(/{{\s*(\d+)\s*}}/g, function (match, num) {
                    num = parseInt(num, 10);
                    if (typeof seen[num] === "undefined") {
                        seen[num] = originalOrder.length + 1;
                        originalOrder.push(num);
                    }
                    return "{{" + seen[num] + "}}";
                });

                if (normalized !== text) {
                    this.bodyText = normalized;
                }

                if (!originalOrder.length) {
                    this.bodyVariables = [];
                    this.syncingBody = false;
                    return;
                }

                this.bodyVariables = originalOrder.map(function (oldIndex, position) {
                    return {
                        index: position + 1,
                        value: currentValues[oldIndex] || "",
                    };
                });

                this.syncingBody = false;
                this.$nextTick(this.refreshSelectpickers);
            },
            onMediaFileChange: function (event) {
                var file = event && event.target && event.target.files ? event.target.files[0] : null;
                this.headerMediaFile = file;
                this.clearMediaPreviewObjectUrl();
                this.clearValidationError("header_media_file");
                this.clearValidationError("header_media_url");
                if (!file) {
                    this.headerMediaPreviewUrl = this.headerMediaUrl || "";
                    return;
                }

                var url = window.URL.createObjectURL(file);
                this.mediaObjectUrl = url;
                this.headerMediaPreviewUrl = url;
            },
            onHeaderMediaUrlInput: function () {
                this.clearValidationError("header_media_url");
                this.clearValidationError("header_media_file");
                if (!this.headerMediaFile) {
                    this.headerMediaPreviewUrl = this.headerMediaUrl || "";
                }
            },
            clearMediaPreviewObjectUrl: function () {
                if (this.mediaObjectUrl) {
                    window.URL.revokeObjectURL(this.mediaObjectUrl);
                    this.mediaObjectUrl = null;
                }
            },
            addButton: function () {
                if (this.buttons.length >= 3) {
                    alert_float("warning", "Maximum 3 buttons allowed");
                    return;
                }

                this.buttons.push({
                    uid: createUid(),
                    type: "QUICK_REPLY",
                    text: "",
                    url: "",
                    phone_number: "",
                });
                this.buttonErrors.push({});
                this.$nextTick(this.refreshSelectpickers);
            },
            removeButton: function (index) {
                this.buttons.splice(index, 1);
                this.buttonErrors.splice(index, 1);
                this.$nextTick(this.refreshSelectpickers);
            },
            onButtonTypeChange: function (button) {
                button.type = normalizeType(button.type);
                if (button.type === "QUICK_REPLY") {
                    button.url = "";
                    button.phone_number = "";
                } else if (button.type === "URL") {
                    button.phone_number = "";
                } else if (button.type === "PHONE_NUMBER") {
                    button.url = "";
                }
                this.$nextTick(this.refreshSelectpickers);
            },
            clearButtonError: function (index, field) {
                if (this.buttonErrors[index] && this.buttonErrors[index][field]) {
                this.$delete(this.buttonErrors[index], field);
                }
            },
            clearValidationError: function (field) {
                if (this.validationErrors && this.validationErrors[field]) {
                    this.$delete(this.validationErrors, field);
                }
            },
            getButtonError: function (index, field) {
                return (this.buttonErrors[index] && this.buttonErrors[index][field]) || "";
            },
            validateTemplateBuilder: function () {
                var errors = [];
                var validationErrors = {};
                var buttonErrors = [];
                var templateName = String(this.templateName || $("#template_name").val() || "").trim();
                var headerFormat = String(this.headerFormat || "NONE").toUpperCase();
                if (!headerFormat || ["NONE", "TEXT", "IMAGE", "VIDEO", "DOCUMENT"].indexOf(headerFormat) === -1) {
                    headerFormat = "NONE";
                }
                var headerText = String(this.headerDataText || "").trim();
                var bodyText = String(this.bodyText || "").trim();
                var footerText = String(this.footerData || "").trim();
                var category = String($("#category").val() || "MARKETING").trim();
                var language = String($("#language").val() || "").trim();

                if (!templateName) {
                    errors.push("Template name is required.");
                    validationErrors.template_name = "This field is required";
                } else if (!/^[a-z0-9_]+$/.test(templateName)) {
                    errors.push("Template name must use lowercase letters, numbers, and underscores only.");
                    validationErrors.template_name = "Use lowercase letters, numbers, and underscores only";
                } else if (templateName.length > 512) {
                    errors.push("Template name must not exceed 512 characters.");
                    validationErrors.template_name = "Max 512 characters";
                }

                if (!language) {
                    errors.push("Language is required.");
                    validationErrors.language = "This field is required";
                }

                if (["MARKETING", "UTILITY", "AUTHENTICATION"].indexOf(category) === -1) {
                    errors.push("Category is invalid.");
                    validationErrors.category = "This field is required";
                }

                if (["NONE", "TEXT", "IMAGE", "VIDEO", "DOCUMENT"].indexOf(headerFormat) === -1) {
                    errors.push("Header type is invalid.");
                }

                if (headerFormat === "TEXT") {
                    if (!headerText) {
                        errors.push("Header text is required for text headers.");
                        validationErrors.header_data_text = "This field is required";
                    } else if (headerText.length > this.headerMaxLength) {
                        errors.push("Header text must not exceed 60 characters.");
                        validationErrors.header_data_text = "Max 60 characters";
                    }
                    if ((headerText.match(/{{\s*1\s*}}/g) || []).length > 1) {
                        errors.push("Text header supports only one variable.");
                        validationErrors.header_data_text = "Only one variable allowed";
                    }
                    if (headerText.indexOf("{{1}}") !== -1 && !String(this.headerVariable1 || "").trim()) {
                        errors.push("Header variable example value is required.");
                        validationErrors.header_variable_1 = "This field is required";
                    }
                }

                if (["IMAGE", "VIDEO", "DOCUMENT"].indexOf(headerFormat) !== -1) {
                    var mediaUrl = String(this.headerMediaUrl || "").trim();
                    var hasMediaFile = !!this.headerMediaFile;
                    var hasMediaUrl = mediaUrl !== "";

                    if (!hasMediaFile && !hasMediaUrl) {
                        errors.push(headerFormat.charAt(0) + headerFormat.slice(1).toLowerCase() + " file or URL is required.");
                        validationErrors.header_media_file = "This field is required";
                        validationErrors.header_media_url = "This field is required";
                    } else if (!hasMediaFile && hasMediaUrl && !isValidHttpUrl(mediaUrl)) {
                        errors.push(headerFormat.charAt(0) + headerFormat.slice(1).toLowerCase() + " URL must be valid.");
                        validationErrors.header_media_url = "Enter a valid URL";
                    } else if (hasMediaFile && !hasMediaUrl) {
                        validationErrors.header_media_url = "";
                    }
                }

                if (!bodyText) {
                    errors.push("Message body is required.");
                    validationErrors.body_data = "This field is required";
                }

                var vars = countBodyVariables(bodyText);
                if (vars.length) {
                    var expected = vars.slice().sort(function (a, b) { return a - b; });
                    if (expected.join(",") !== vars.join(",")) {
                        errors.push("Body variables must be sequential and start from {{1}}.");
                        validationErrors.body_data = "Variables must start from {{1}} and stay sequential";
                    }
                    var missingBodyVariable = false;
                    vars.forEach(function (num) {
                        var row = this.bodyVariables.find(function (item) {
                            return parseInt(item.index, 10) === num;
                        });
                        if (!row || !String(row.value || "").trim()) {
                            errors.push("Body variable {{" + num + "}} example value is required.");
                            missingBodyVariable = true;
                        }
                    }.bind(this));
                    if (missingBodyVariable) {
                        validationErrors.body_variables = "This field is required";
                    }
                }

                if (footerText.length > this.footerMaxLength) {
                    errors.push("Footer text must not exceed 60 characters.");
                    validationErrors.footer_data = "Max 60 characters";
                }

                if (this.buttons.length > 3) {
                    errors.push("WhatsApp template supports a maximum of 3 buttons.");
                }

                this.buttons.forEach(function (button, index) {
                    buttonErrors[index] = {};
                    button.type = normalizeType(button.type);
                    if (!String(button.text || "").trim()) {
                        errors.push("Button " + (index + 1) + " text is required.");
                        buttonErrors[index].text = "This field is required";
                    } else if (String(button.text || "").length > 25) {
                        errors.push("Button " + (index + 1) + " text must not exceed 25 characters.");
                        buttonErrors[index].text = "Max 25 characters";
                    }
                    if (button.type === "URL" && !String(button.url || "").trim()) {
                        errors.push("Button " + (index + 1) + " website URL is required.");
                        buttonErrors[index].url = "This field is required";
                    } else if (button.type === "URL" && !isValidHttpUrl(button.url)) {
                        errors.push("Button " + (index + 1) + " website URL must be valid.");
                        buttonErrors[index].url = "Enter a valid URL";
                    }
                    if (button.type === "PHONE_NUMBER" && !String(button.phone_number || "").trim()) {
                        errors.push("Button " + (index + 1) + " phone number is required.");
                        buttonErrors[index].phone_number = "This field is required";
                    } else if (button.type === "PHONE_NUMBER" && !/^\+?[0-9]{6,15}$/.test(String(button.phone_number || "").trim())) {
                        errors.push("Button " + (index + 1) + " phone number must contain 6 to 15 digits and may start with +.");
                        buttonErrors[index].phone_number = "Enter a valid phone number";
                    }
                });

                this.buttonErrors = buttonErrors;
                this.validationErrors = validationErrors;

                if (errors.length) {
                    if ($.fn && typeof $.fn.selectpicker === "function") {
                        $(".selectpicker").selectpicker("refresh");
                    }
                    return false;
                }

                return true;
            },
            submitTemplate: function () {
                if (!this.validateTemplateBuilder()) {
                    return;
                }

                var form = document.getElementById("template_builder_form");
                var formData = new FormData(form);
                var self = this;

                $.ajax({
                    url: $(form).attr("action"),
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response && response.status) {
                            alert_float("success", response.message || "Template submitted to WhatsApp for approval.");
                            setTimeout(function () {
                                window.location.href = admin_url + "whatsbot/templates";
                            }, 1500);
                        } else {
                            alert_float("danger", (response && response.message) || "Error creating template.");
                        }
                    },
                    error: function () {
                        alert_float("danger", "Error creating template.");
                    },
                    complete: function () {
                        self.refreshSelectpickers();
                    },
                });
            },
        },
    });
})(jQuery, window.Vue);
