// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

define([
    "jquery",
    "core/notification",
    "core/ajax"
], function ($, Notification, Ajax) {
    "use strict";

    return {
        init: function (localStrings, action) {
            var SELECT = {
                contextlevel: $('#id_contextlevel'),
                role: $('#id_role'),
                instance: $('#id_instance')
            };

            var BUTTON = {
                submit: $('#id_submitbutton')
            };

            var CONTEXT_LEVEL = {
                system: "10",
                user: "30",
                coursecat: "40",
                course: "50",
                module: "70",
                block: "80"
            };

            var resetSelect = function ($selectEl) {
                $selectEl.find("option").remove().end();
            };

            var resetSelects = function (selects) {
                selects.forEach(function ($el) {
                    resetSelect($el);
                });
            };

            var enableElement = function ($el) {
                $el.prop("disabled", false);
                $el.removeClass('disabled');
            };

            var enableElements = function (els) {
                els.forEach(function ($el) {
                    enableElement($el);
                });
            };

            var disableElement = function ($el) {
                $el.prop("disabled", true);
                $el.addClass('disabled');
            };

            var disableElements = function (els) {
                els.forEach(function ($el) {
                    disableElement($el);
                });
            };

            var loadOptions = function ($selectEl, options) {
                options.forEach(function (item) {
                    $selectEl.append($('<option>', {
                        value: item.value,
                        text: item.text
                    }));
                });

                $selectEl.trigger('change');
            };

            var allCategoriesStr = localStrings.allcategories || 'All course categories',
                allCoursesStr = localStrings.allcourses || 'All courses';

            var actionMethodName;
            if (action === 'assign') {
                actionMethodName = 'tool_userbulkrolechange_bulk_assign_role';
            } else {
                actionMethodName = 'tool_userbulkrolechange_bulk_unassign_role';
            }

            var onContextLevelChange = function () {
                disableElements([BUTTON.submit, SELECT.role, SELECT.instance]);
                resetSelects([SELECT.role, SELECT.instance]);

                var contextLevel = SELECT.contextlevel.val(),
                    instanceMethodname,
                    instanceDataKey,
                    instanceFirstOption,
                    calls;

                switch (contextLevel) {
                    case CONTEXT_LEVEL.coursecat:
                        instanceMethodname = "tool_userbulkrolechange_get_all_course_categories";
                        instanceDataKey = "course_categories";
                        instanceFirstOption = {
                            value: -1,
                            text: allCategoriesStr
                        };
                        break;
                    case CONTEXT_LEVEL.course:
                        instanceMethodname = "tool_userbulkrolechange_get_all_courses";
                        instanceDataKey = "courses";
                        instanceFirstOption = {
                            value: -1,
                            text: allCoursesStr
                        };
                        break;
                    default:
                        instanceMethodname = false;
                        break;
                }

                calls = [{
                    methodname: "tool_userbulkrolechange_get_context_level_roles",
                    args: {contextlevel: contextLevel}
                }];

                if (instanceMethodname) {
                    calls.push({
                        methodname: instanceMethodname,
                        args: {}
                    });
                }

                var promises = Ajax.call(calls);

                promises[0].then(function (data) {
                    if (data.roles) {
                        loadOptions(SELECT.role, data.roles);
                        enableElement(SELECT.role);
                    }
                }, Notification.exception);

                if (instanceMethodname) {
                    promises[1].then(function (data) {
                        if (data[instanceDataKey]) {
                            data[instanceDataKey].unshift(instanceFirstOption);
                            loadOptions(SELECT.instance, data[instanceDataKey]);
                            enableElement(SELECT.instance);
                        }
                    }, Notification.exception);
                }

                $.when.apply($, promises).done(function () {
                    enableElement(BUTTON.submit);
                });
            };

            var onSubmitClick = function () {
                disableElements([BUTTON.submit, SELECT.contextlevel, SELECT.role, SELECT.instance]);

                var contextLevel = SELECT.contextlevel.val(),
                    role = SELECT.role.val(),
                    instance = SELECT.instance.val();

                Ajax.call([{
                    methodname: actionMethodName,
                    args: {
                        contextlevel: contextLevel,
                        role: role,
                        instance: instance
                    }
                }])[0].then(function (data) {
                    if (data.success) {
                        // Notify success
                    }
                    enableElements([BUTTON.submit, SELECT.contextlevel, SELECT.role, SELECT.instance]);
                }, Notification.exception);

                return false;
            };

            SELECT.contextlevel.on("change", onContextLevelChange);
            BUTTON.submit.on("click", onSubmitClick);
            enableElement(BUTTON.submit);
        }
    };
});
