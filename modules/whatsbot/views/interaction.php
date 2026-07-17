<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<link rel="stylesheet"
    href="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/css/chat.css') . '?v=' . $module_version; ?>">
<link rel="stylesheet"
    href="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/css/whatsbot_tailwind.css') . '?v=' . $module_version; ?>">
<link rel="stylesheet"
    href="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/css/location/leaflet.css') . '?v=' . $module_version; ?>">
<link rel="stylesheet"
    href="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/css/location/leaflet_fullscreen.css') . '?v=' . $module_version; ?>">
<style>
    .slash-command-dropdown {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        max-height: 300px;
    }

    .dark .slash-command-dropdown {
        border-color: #4a5568;
    }

    #canned_replies,
    #slash_command_replies {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
        max-height: 60vh;
        /* Limit height on smaller screens */
    }

    .grid-cols-1>div,
    .grid-cols-2>div {
        transition: all 0.1s ease-in-out;
        position: relative;
    }

    .grid-cols-1>div:hover,
    .grid-cols-2>div:hover {
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {

        #canned_replies,
        #slash_command_replies {
            left: 0;
            width: 100%;
            max-width: 100%;
            bottom: 80px;
        }
    }
</style>
<?php init_head(); ?>

<?php
if (empty(get_option('pusher_app_key')) || empty(get_option('pusher_app_secret')) || empty(get_option('pusher_app_id')) || empty(get_option('pusher_cluster'))) { ?>
    <div id="wrapper" class="tw-h-full tw-flex tw-items-center tw-justify-center">
        <div class="tw-container">
            <div class="tw-flex tw-justify-center">
                <div class="tw-w-full tw-max-w-md">
                    <div class="tw-bg-white tw-shadow-md tw-rounded-lg tw-p-6">
                        <div class="tw-text-center tw-mb-4">
                            <h3 class="tw-text-xl tw-font-semibold">Pusher Account Setup</h3>
                        </div>
                        <div class="tw-text-center">
                            <h4 class="tw-text-lg tw-mb-4">It seems that your Pusher account is not configured correctly.
                            </h4>
                            <p class="tw-mb-4">Please configure your Pusher account:</p>
                            <a href="<?= admin_url('settings?group=pusher'); ?>"
                                class="tw-bg-blue-500 tw-py-2 tw-px-4 tw-rounded tw-w-full tw-block tw-mb-4">Perfex CRM
                                Settings → Pusher.com</a>
                            <p class="tw-mb-4">For guidance, you can follow this tutorial:</p>
                            <a href="https://help.perfexcrm.com/setup-realtime-notifications-with-pusher-com/"
                                target="_blank" class="tw-text-blue-500">See how to set up Pusher from Perfex CRM
                                documentation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php init_tail(); ?>
<?php exit;
}
?>
<?php
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

?>
<div id="wrapper">
    <div id="app" class="p-2" :class="{ dark: darkMode }" v-cloak>
        <div v-if="errorMessage"
            class="bg-red-200 border border-red-600 text-red-800 p-2 rounded-md  w-full flex justify-between items-center"
            v-cloak>
            <p class="text-sm">{{ errorMessage }}</p>
            <button class="hideMessage text-red-600 hover:text-red-800 focus:outline-none">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div v-if="sameNumErrorMessage"
            class="bg-red-200 border border-red-600 text-red-800 w-full p-2 rounded-md flex justify-between items-center"
            v-cloak>
            <p class="text-sm">{{ sameNumErrorMessage }}</p>
            <button class="hideMessage text-red-600 hover:text-red-800 focus:outline-none">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div>

            <div class="flex gap-2 relative sm:h-[calc(100vh_-_90px)] h-full sm:min-h-0 "
                :class="{ 'min-h-[999px]': isShowChatMenu }" v-cloak>
                <!-- Sidebar Start -->
                <div class="panel dark:bg-[#1E293B] p-2 flex-none max-w-sm w-full absolute xl:relative z-10 space-y-4 h-full overflow-hidden mainsidebar-class"
                    :class="isShowChatMenu && '!block !overflow-y-auto'" v-cloak>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="flex-none">
                                <img class="rounded-full h-12 w-12 object-cover"
                                    src="<?= !empty(get_option("wac_profile_picture_url")) ? get_option("wac_profile_picture_url") : base_url('assets/images/user-placeholder.jpg'); ?>"
                                    alt="profile">
                            </div>
                            <div class="mx-3"
                                v-if="wb_selectedinteraction && typeof wb_selectedinteraction === 'object'">
                                <span class="text-md dark:text-gray-200"><?php echo _l('from'); ?> {{
                                    wb_selectedinteraction.wa_no }}</span>
                            </div>
                        </div>

                        <div class="flex justify-end items-center ml-auto cursor-pointer gap-4">
                            <button type="button" class="xl:hidden" v-on:click="isShowChatMenu = !isShowChatMenu">
                                <i class="fa-regular fa-xl fa-xmark fa-xmark-circle"></i>
                            </button>

                            <img class="rounded-full w-4 h-4" v-if="!darkMode" v-on:click="toggleDarkMode"
                                src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/moon.png'); ?>"
                                alt="moon">
                            <img class="rounded-full w-6 h-6" v-if="darkMode" v-on:click="toggleDarkMode"
                                src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/sun.png'); ?>" alt="sun">
                        </div>
                    </div>

                    <div class="flex items-center" v-cloak>
                        <div class="flex justify-between items-center w-full">
                            <select v-model="wb_selectedWaNo" v-on:change="wb_filterInteractions" id="wb_selectedWaNo"
                                class="w-full rounded-md border-0 p-2 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-inset dark:bg-gray-800 dark:text-gray-200 focus:ring-blue-500 sm:text-sm sm:leading-6 mr-2">
                                <option v-for="(interaction, index) in wb_uniqueWaNos" :key="index"
                                    :value="interaction.wa_no" class="bg-[#F0F2F5] dark:bg-gray-700 dark:text-gray-200"
                                    :selected="wb_selectedWaNo === 'interaction.wa_no'">
                                    {{ interaction.wa_no }}
                                </option>
                                <option value="*"><?php echo _l('all_chat'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center" v-cloak>
                        <div class="flex justify-around items-center w-full gap-2">
                            <button
                                class="tab-button px-2 py-2 rounded-md text-xs font-medium transition-colors duration-200 ease-in-out w-full"
                                :class="selectedTab === 'searching' ? 'bg-blue-500 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'"
                                v-on:click="resetFilters">
                                <i class="fa fa-search dark:text-gray-200"></i> <?php echo _l('searching'); ?>
                            </button>

                            <button
                                class="tab-button px-4 py-2 rounded-md text-xs font-medium transition-colors duration-200 ease-in-out w-full"
                                :class="selectedTab === 'filters' ? 'bg-blue-500 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'"
                                v-on:click="selectedTab = 'filters'">
                                <i class="fa fa-filter dark:text-gray-200"></i> <?php echo _l('filters'); ?>
                            </button>
                        </div>
                    </div>
                    <div v-if="selectedTab === 'searching'" v-cloak>
                        <div class="flex justify-between items-center w-full ">
                            <input id="wb_searchText" type="text" name="wb_searchText"
                                class="form-input outline-none rounded-md p-2 w-full  dark:bg-[#1E293B] dark:text-gray-200 dark:placeholder-gray-400 placeholder-gray-600"
                                placeholder="Searching..." v-model="wb_searchText" />
                            <div class="relative">
                                <div class="absolute right-[30px] top-1/2 -translate-y-1/2 ">
                                    <i class="fa fa-search dark:text-gray-200"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- Tab content for filters -->
                    <div v-if="selectedTab === 'filters'" v-cloak>
                        <div class="flex items-center">
                            <div class="flex justify-between flex-col items-center w-full gap-2">
                                <div class="flex justify-between items-center gap-2 w-full">
                                    <!-- Relation Type Dropdown -->
                                    <div class="w-full" v-cloak>
                                        <label for="relationType"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <?php echo _l('relation_type'); ?>
                                        </label>
                                        <select id="relationType" v-on:change="wb_handleAllFilters"
                                            v-model="wb_reltype_filter"
                                            class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_type'); ?></option>
                                            <option v-for="relType in wb_requireData?.rel_types" :key="relType.key"
                                                :value="relType.key" class="text-gray-900 dark:text-gray-200">
                                                {{ relType.value }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="w-full" v-cloak>
                                        <label for="agents"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <?php echo _l('agents'); ?>
                                        </label>
                                        <select id="agents" v-on:change="wb_handleAllFilters" v-model="wb_agents_filter"
                                            class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_agents'); ?></option>
                                            <option v-for="agent in wb_requireData?.agents" :key="agent.staffid"
                                                :value="agent.staffid" class="text-gray-900 dark:text-gray-200">
                                                {{ agent.firstname + ' ' + agent.lastname }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="w-full" v-cloak>
                                    <label for="labels"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                        <?php echo _l('label'); ?>
                                    </label>
                                    <select id="labels" v-on:change="wb_handleAllFilters" v-model="wb_labels_filter"
                                        class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                        <option value=""><?php echo _l('select_label'); ?></option>
                                        <option v-for="label in wb_requireData?.custom_labels" :key="label.id"
                                            :value="label.id" class="text-gray-900 dark:text-gray-200">
                                            {{label.label}}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex justify-between items-center gap-2 w-full" v-cloak>
                                    <!-- Groups Dropdown -->
                                    <div class="w-full" v-if="wb_reltype_filter === 'contacts'">
                                        <label for="groups"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <?php echo _l('groups'); ?></label>
                                        <select id="groups" v-on:change="wb_handleAllFilters"
                                            v-model="wb_contact_groups"
                                            class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_group'); ?></option>
                                            <option v-for="groups in wb_requireData?.contact_groups" :key="groups.id"
                                                :value="groups.id" class="text-gray-900 dark:text-gray-200">
                                                {{ groups.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <!-- Source Dropdown -->
                                    <div class="w-full" v-if="wb_reltype_filter === 'leads'" v-cloak>
                                        <label for="source"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <?php echo _l('source'); ?></label>
                                        <select id="source" v-on:change="wb_handleAllFilters" v-model="wb_lead_sources"
                                            class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_source'); ?></option>
                                            <option v-for="source in wb_requireData?.lead_sources" :key="source.id"
                                                :value="source.id" class="text-gray-900 dark:text-gray-200">
                                                {{ source.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Status Dropdown -->
                                    <div class="w-full" v-if="wb_reltype_filter === 'leads'" v-cloak>
                                        <label for="status"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <?php echo _l('status'); ?></label>
                                        <select id="status" v-on:change="wb_handleAllFilters" v-model="wb_lead_status"
                                            class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_status'); ?></option>
                                            <option v-for="status in wb_requireData?.lead_status" :key="status.id"
                                                :value="status.id" class="text-gray-900 dark:text-gray-200">
                                                {{ status.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="h-px w-full custom-border" v-cloak></div>
                    <div v-if="noResultsMessage" v-html="noResultsMessage" class="text-red-500" v-cloak>
                    </div>

                    <div :class="selectedTab === 'filters' ? 'h-full min-h-[100px] sm:h-[calc(100vh_-_358px)] overflow-y-auto' : 'h-full min-h-[100px] sm:h-[calc(100vh_-_340px)] overflow-y-auto'"
                        v-cloak @scroll="onSidebarScroll($event)" ref="chatSidebar">
                        <div class=" hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer custom-border rounded"
                            v-for="(interaction, index) in wb_displayedInteractions" :key="interaction.id"
                            v-on:click="wb_selectinteraction(interaction.id)"
                            :class="{'bg-gray-200': wb_selectedinteraction && wb_selectedinteraction.id === interaction.id,'dark:bg-gray-700': wb_selectedinteraction && wb_selectedinteraction.id === interaction.id}">
                            <div class="flex items-center gap-2 w-full">
                                <div class="p-2 flex items-center justify-center">
                                    <p :title="interaction.receiver_id" data-toggle="tooltip" data-placement="top"
                                        class="rounded-full bg-green-400 w-12 h-12 flex items-center justify-center text-center font-semibold text-gray-700">
                                        {{ wb_getAvatarInitials(interaction.name) }}
                                    </p>
                                </div>
                                <div class="side-chat flex justify-between w-[75%] ">
                                    <div class="chat-name flex flex-col">
                                        <div class="flex justify-1 items-center gap-2">
                                            <h5 :title="interaction.receiver_id" data-toggle="tooltip"
                                                data-placement="top"
                                                class="text-md text-gray-700 font-sans font-semibold dark:text-gray-200 truncate" style="width: 130px;">
                                                {{ interaction.name }}
                                            </h5>
                                            <p
                                                class="text-md text-gray-500 font-sans font-normal flex items-center mb-1">
                                                <span :class="{
                                                        'bg-violet-100 text-purple-800': interaction.type === 'leads',
                                                        'bg-red-100 text-red-800': interaction.type === 'contacts',
                                                    }" class="inline-block mt-1 text-xs font-semibold px-2 rounded ">
                                                    {{ interaction.type }}
                                                </span>
                                            </p>
                                        </div>
                                        <span v-html="formatInteractionPreview(interaction)"
                                            class="dark:text-gray-200"></span>

                                    </div>
                                    <div class="flex flex-col gap-2 items-end relative">
                                        <p
                                            class="text-[0.80rem] font-sans text-gray-500 font-normal dark:text-gray-400">
                                            {{ wb_formatTime(interaction.time_sent)
                                            }}
                                        </p>
                                        <div class="flex gap-2 justify-center items-center ">
                                            <span v-on:click="wb_deleteInteraction(interaction.id)" class="dele-icn"><i
                                                    class="fa-solid text-red-500 float-right fa-trash"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="<?php echo _l('remove_chat'); ?>"></i>
                                            </span>

                                            <span v-if="wb_countUnreadMessages(interaction.id) > 0"
                                                class="bg-green-500 text-white text-xs font-semibold py-1 px-2 rounded-full">
                                                {{ wb_countUnreadMessages(interaction.id) }}
                                            </span>

                                        </div>
                                        <p v-if="interaction.label"
                                            class="text-md text-gray-500 font-sans font-normal flex items-center mb-1 absolute"
                                            style="top: 67%;right:67px;">
                                            <span :style="{ color: labelColor[index] }"
                                                class="inline-block text-xs font-semibold px-2 rounded bg-slate-200 truncate"
                                                style="width:50px;">
                                                {{ labelName[index] }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar end -->
                <!-- Main content start-->
                <div class="panel flex-1 h-full dark:bg-[#1E293B]" v-cloak>
                    <template v-if="!isShowUserChat">
                        <div class="flex items-center justify-center h-full relative p-4">
                            <button type="button"
                                class="xl:hidden absolute top-4 ltr:left-4 rtl:right-4 hover:text-primary dark:text-gray-200"
                                v-on:click="isShowChatMenu = !isShowChatMenu">
                                <i class="fa fa-align-left fa-xl"></i>
                            </button>
                            <div class="py-8 flex items-center justify-center flex-col">
                                <div
                                    class="w-[280px] md:w-[430px] mb-8 h-[calc(100vh_-_320px)] min-h-[120px] text-white dark:text-[#0e1726]">
                                    <img class=" w-full h-full" v-if="!darkMode"
                                        src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/light_mob.svg'); ?>"
                                        alt="light">
                                    <img class=" w-full h-full" v-if="darkMode"
                                        src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/dark_mob.svg'); ?>"
                                        alt="dark">
                                </div>
                                <div
                                    class="flex justify-center item-center gap-4 p-2 font-semibold rounded-md max-w-[190px] mx-auto dark:text-gray-400">
                                    <span><?= _l('click_user_to_chat'); ?></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template>
                        <div v-if="wb_selectedinteraction && typeof wb_selectedinteraction === 'object'"
                            class="relative h-full " v-cloak>
                            <div
                                class="flex justify-between items-center p-2 bg-white dark:bg-[#1E293B] rounded relative gap-2">
                                <!-- 1st -->
                                <div class="flex justify-between items-center space-x-4">
                                    <button type="button" class="xl:hidden dark:text-gray-200"
                                        v-on:click="isShowChatMenu = !isShowChatMenu">
                                        <i class="fa fa-align-left fa-xl"></i>
                                    </button>
                                    <span
                                        class="rounded-full w-12 h-12 flex items-center justify-center text-center font-semibold text-gray-700 bg-green-400">
                                        {{wb_getAvatarInitials(wb_selectedinteraction.name) }}
                                    </span>
                                    <div class="flex flex-col">
                                        <div class="flex justify-start items-center gap-4">
                                            <button v-on:click='wb_typeModal_Open'><span
                                                    :title="'Click to open ' + wb_selectedinteraction.type"
                                                    data-toggle="tooltip" data-placement="top"
                                                    class="text-slate-600 font-sans text-nowrap font-medium text-base dark:text-slate-200 hover:text-blue-900">{{
                                                    wb_selectedinteraction.name }}</span></button>
                                            <div>
                                            </div>
                                        </div>
                                        <span
                                            class="text-slate-400 flex items-center justify-start gap-2 font-sans font-medium text-[0.75rem]">+{{
                                            wb_selectedinteraction.receiver_id }}</span>
                                    </div>
                                </div>
                                <!-- 2nd -->
                                <div class="flex flex-1 justify-start items-start gap-2" v-cloak>
                                    <div class="flex flex-col justify-start items-start gap-2 relative ">
                                        <div class="mainsidebar-badge">
                                            <div class="flex justify-start items-start gap-2 mb-4">
                                                <span :class="{
                                                        'bg-violet-100 text-purple-800': wb_selectedinteraction.type === 'leads',
                                                        'bg-red-100 text-red-800': wb_selectedinteraction.type === 'contacts',
                                                    }" class="inline-block  text-xs font-semibold px-2 rounded ">
                                                    {{ wb_selectedinteraction.type }}
                                                </span>
                                                <span v-if="wb_selectedinteraction.type === 'leads'"
                                                    class="inline-block  text-xs font-semibold px-2 rounded bg-sky-200 text-sky-800">
                                                    {{ badgeSourceName }}
                                                </span>
                                                <span v-if="wb_selectedinteraction.type === 'leads'"
                                                    :style="{ color: badgeStatusColor }"
                                                    class="inline-block  text-xs font-semibold px-2 rounded bg-slate-200">
                                                    {{ badgeStatusName }}
                                                </span>
                                                <span v-if="wb_selectedinteraction.type === 'contacts'"
                                                    v-for="(groupName, index) in badgeGroupNames" :key="index"
                                                    class="inline-block text-xs font-semibold px-2 rounded bg-blue-100 text-blue-800 ">
                                                    {{ groupName }}
                                                </span>
                                                <span v-if="wb_selectedinteraction.label"
                                                    :style="{ color: labelColor[wb_selectedinteractionDisplayedIndex]}"
                                                    class="inline-block text-xs font-semibold px-2 rounded bg-slate-200">
                                                    {{ labelName[wb_selectedinteractionDisplayedIndex]}}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mainsidebar-class">
                                            <div v-if="wb_selectedinteraction.last_msg_time"
                                                class="w-[422px] flex justify-end items-center absolute top-[20px] left-[212px]">
                                                <span v-html="wb_alertTime(wb_selectedinteraction.last_msg_time)"
                                                    class="text-green-500">
                                                </span>
                                                <span v-if="wb_isServiceWindowOpen(wb_selectedinteraction.last_msg_time)"
                                                    class="ml-2 text-xs px-2 py-1 rounded-full bg-green-100 text-green-700"
                                                    title="<?php echo _l('service_window_open'); ?>">
                                                    <i class="fa fa-check-circle"></i> <?php echo _l('service_window_open'); ?>
                                                </span>
                                                <span v-else
                                                    class="ml-2 text-xs px-2 py-1 rounded-full bg-red-100 text-red-700"
                                                    title="<?php echo _l('service_window_closed'); ?>">
                                                    <i class="fa fa-lock"></i> <?php echo _l('service_window_closed'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- message searching -->
                                <button type="button" v-on:click="toggleMessageSearch"
                                    title="<?php echo _l('searching'); ?>" data-toggle="tooltip" data-placement="left"
                                    class="dark:text-gray-200">
                                    <i class="fa fa-search dark:tw-text-success-400 fa-lg tw-text-success-600"></i>
                                </button>

                                <!-- Message Search Modal -->
                                <template>
                                    <transition name="fade">
                                        <div v-show="wb_messagesSearch" class="absolute z-10"
                                            style="top: 70px;right: 35%;">
                                            <!-- Search Input -->
                                            <div class="w-[422px] px-4">
                                                <div class="relative">
                                                    <input type="text" v-model="searchMessagesText"
                                                        @input="searchMessages" ref="searchInput"
                                                        class="w-full border shadow rounded-full text-gray-800 border-gray-300 bg-white dark:text-gray-200 dark:border-gray-700 dark:bg-gray-800 outline-none p-2 pr-12"
                                                        placeholder="Search Messages..." />
                                                    <div v-if="matchedMessages.length > 0"
                                                        class="ml-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <span id="search-counter"></span>
                                                    </div>

                                                    <!-- Previous Match -->
                                                    <button v-if="matchedMessages.length > 0" v-on:click="prevMatch"
                                                        class="absolute  text-gray-400 dark:text-gray-300"
                                                        style="top: 0.5rem;right: 0.9rem;">
                                                        <i class="fas fa-chevron-up "></i>
                                                    </button>

                                                    <!-- Next Match -->
                                                    <button v-if="matchedMessages.length > 0" v-on:click="nextMatch"
                                                        class="absolute  text-gray-400 dark:text-gray-300"
                                                        style="top: 1.2rem;right: 0.9rem;">
                                                        <i class="fas fa-chevron-down "></i>
                                                    </button>


                                                    <!-- Close Button -->
                                                    <button class="absolute text-gray-500 dark:text-gray-300"
                                                        style="top: 0.6rem;right: -1.50rem;"
                                                        v-on:click="wb_messagesSearch = false; searchMessagesText = '', searchError=''">
                                                        <i class="fas fa-xmark text-xl"></i>
                                                    </button>
                                                </div>

                                                <!-- Error Message -->
                                                <p v-if="searchError" class="text-red-500 text-xs mt-2">{{ searchError
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </transition>
                                </template>
                                <button type="button" v-on:click="wb_initLabel"
                                    title="<?php echo _l('change_labels'); ?>" data-toggle="tooltip"
                                    data-placement="left" class="dark:text-gray-200">
                                    <i class="fa-solid fa-tag fa-lg"></i>
                                </button>
                                <!-- 3rd -->
                                <?php if (is_admin() && get_option('enable_supportagent')) { ?>
                                    <div class="flex gap-3 items-center justify-end ">
                                        <div v-if="wb_selectedinteraction.agent_name.agent_name" class="flex items-center">

                                            <div class="inline-flex items-center space-x-2">
                                                <div class="flex -space-x-1" v-html="wb_selectedinteraction.agent_icon">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" v-on:click="wb_initAgent" class="dark:text-gray-200">
                                            <i class="fa-solid fa-user-pen fa-lg" data-toggle="tooltip"
                                                data-placement="left" title="<?php echo _l('change_support_agent'); ?>"></i>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="mainsidebar-class" v-show="!wb_messagesSearch" v-cloak>
                                <div
                                    class="w-[422px] flex justify-end items-center absolute top-[70px] left-[33%] z-10 ">
                                    <div v-if="overdueAlert" v-html="overdueAlert" v-cloak></div>
                                </div>
                            </div>
                            <div class="modal fade" id="LabelModal" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title"><?php echo _l('select_label'); ?></h4>
                                        </div>
                                        <div class="modal-body">
                                            <?= render_select('label', wb_get_custom_labels(), ['id', 'label']); ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal"><?php echo _l('close_btn'); ?></button>
                                            <button type="button" class="btn btn-primary" data-dismiss="modal"
                                                v-on:click="wb_handleLabelChange"><?php echo _l('save_btn'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if (is_admin()) { ?>
                                <div class="modal fade" id="AgentModal" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title"><?php echo _l('modal_title'); ?></h4>
                                            </div>
                                            <div class="modal-body">

                                                <?= render_select(
                                                    'assigned[]',
                                                    $members,
                                                    ['staffid', ['firstname', 'lastname']],
                                                    '',
                                                    '',
                                                    ['data-width' => '100%', 'multiple' => true, 'data-actions-box' => true],
                                                    [],
                                                    '',
                                                    '',
                                                    false
                                                ); ?>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal"><?php echo _l('close_btn'); ?></button>
                                                <button type="button" class="btn btn-primary" data-dismiss="modal"
                                                    v-on:click="wb_handleAssignedChange"><?php echo _l('save_btn'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div :class="['flex flex-col', is_pemission_replay_input ? 'sm:h-[calc(100vh_-_263px)]' : 'sm:h-[calc(100vh_-_156px)]']"
                                v-cloak>
                                <!-- chat-section Start -->
                                <div class="relative h-full overflow-y-auto" :class="[
                                                is_pemission_replay_input ? 'sm:h-[calc(100vh_-_263px)]' : 'sm:h-[calc(100vh_-_156px)]',
                                                darkMode ? 'bg-dark-mode' : 'bg-light-mode'
                                            ]" ref="wb_chatContainer" @scroll="wb_selectedinteraction?.id && checkScrollTop(wb_selectedinteraction.id)">
                                    <div class="space-y-5 p-4 sm:pb-0 pb-[68px] sm:min-h-[300px] min-h-[400px] mobile-view"
                                        v-cloak>
                                        <div v-if="wb_selectedinteraction && wb_selectedinteraction.messages">
                                            <div v-for="(message, index) in wb_selectedinteraction.messages"
                                                :key="index" v-cloak>
                                                <!-- Message from the left -->
                                                <div class="flex justify-center"
                                                    v-if="wb_shouldShowDate(message, wb_selectedinteraction.messages[index - 1])">
                                                    <span
                                                        class="bg-white py-1 px-2 text-xs rounded-md dark:bg-gray-600 dark:text-gray-200">
                                                        {{getDate(message.time_sent) }}
                                                    </span>
                                                </div>
                                                <!-- Internal Note Display -->
                                                <div v-if="message.type === 'internal_note'" class="my-4">
                                                    <!-- Separator Line -->
                                                    <div class="flex items-center text-xs text-yellow-700 mb-1">
                                                        <div class="flex-1" style="border-top: 2px solid #FFC107;"></div>
                                                        <div class="px-3 flex items-center gap-1 font-medium">
                                                            <i class="fa fa-sticky-note mright"></i> <?= _l('internal_note'); ?> • {{ message.staff_name }} • {{ wb_getTime(message.time_sent) }}
                                                        </div>
                                                        <div class="flex-1" style="border-top: 2px solid #FFC107;"></div>
                                                    </div>
                                                    <!-- Note Content -->
                                                    <div class="text-center">
                                                        <span class="inline-block bg-yellow-50 text-yellow-800 text-sm px-3 py-1 rounded-md">
                                                            <span v-html="message.message"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-else-if="message.type !== 'reaction'"
                                                    :class="['flex relative', message.sender_id === wb_selectedinteraction.wa_no ? 'justify-end mb-8 ' : 'justify-start mb-4']">
                                                    <div :class="[
                                                            'border border-gray-300 p-1 break-words rounded-lg max-w-xs',
                                                            message.sender_id === wb_selectedinteraction.wa_no ? 'bg-[#82ee8aa6] dark:bg-[#128C7E]' : 'bg-white dark:bg-[#273443]',
                                                            message.staff_id == 0 && message.sender_id === wb_selectedinteraction.wa_no ? 'bg-[#e7e7e7] dark:bg-[#707070fa]' : '',
                                                            message.type === 'text' && message.message.length > 50 ? 'max-w-xs' : ''
                                                        ]" v-bind="message.sender_id === wb_selectedinteraction.wa_no ? {
                                                            'data-toggle': 'tooltip',
                                                            'data-placement': 'left',
                                                            'title': message.staff_name
                                                        } : {}">

                                                        <!-- Frequently Forwarded -->
                                                        <div v-if="message.is_frequently_forwarded == 1"
                                                            class="text-[11px] text-gray-400 italic mb-1 flex items-center gap-1">

                                                            <i class="fa fa-retweet text-[10px] mright5"></i>
                                                            <span><?= _l('frequently_forwarded') ?></span>

                                                        </div>
                                                        <span v-if="message.sender_id === wb_selectedinteraction.wa_no"
                                                            class="absolute right-0 text-gray-800 dark:text-gray-300 text-sm stf-name font-semibold">
                                                            {{ message.staff_name }}
                                                            <img v-if="message.staff_id != 0"
                                                                :src="message.staff_profile_image"
                                                                class="tw-inline-block tw-h-4 tw-w-4 tw-rounded-full tw-ring-2 tw-ring-black tw-ml-1" />
                                                        </span>
                                                        <template v-if="message.ref_message_id">
                                                            <div
                                                                class="bg-neutral-100 dark:bg-gray-500 rounded-lg mb-2">
                                                                <div class="flex flex-col gap-2 p-2">
                                                                    <span
                                                                        class="text-gray-400 dark:text-gray-400 font-normal pb-0"><?php echo _l('replying_to'); ?></span>
                                                                    <span class="text-gray-800 dark:text-gray-200 pb-0"
                                                                        v-html="getOriginalMessage(message.ref_message_id).message"></span>
                                                                    <div
                                                                        v-if="getOriginalMessage(message.ref_message_id).assets_url">
                                                                        <template
                                                                            v-if="getOriginalMessage(message.ref_message_id).type === 'image'">
                                                                            <a :href="getOriginalMessage(message.ref_message_id).asset_url"
                                                                                data-lightbox="image-group"
                                                                                target="_blank">
                                                                                <img :src="getOriginalMessage(message.ref_message_id).asset_url"
                                                                                    class="rounded-lg max-w-xs max-h-28"
                                                                                    alt="Image">
                                                                            </a>
                                                                        </template>
                                                                        <template
                                                                            v-if="getOriginalMessage(message.ref_message_id).type === 'video'">
                                                                            <video
                                                                                :src="getOriginalMessage(message.ref_message_id).asset_url"
                                                                                controls
                                                                                class="rounded-lg max-w-xs max-h-28"></video>
                                                                        </template>
                                                                        <template
                                                                            v-if="getOriginalMessage(message.ref_message_id).type === 'sticker'">
                                                                            <img
                                                                                :src="getOriginalMessage(message.ref_message_id).asset_url"
                                                                                class="rounded-lg max-w-xs max-h-28 object-contain"
                                                                                alt="Sticker">
                                                                        </template>
                                                                        <template
                                                                            v-if="getOriginalMessage(message.ref_message_id).type === 'document'">
                                                                            <a :href="getOriginalMessage(message.ref_message_id).asset_url"
                                                                                target="_blank"
                                                                                class="text-blue-500 underline"><?php echo _l('download_document'); ?></a>
                                                                        </template>
                                                                        <template
                                                                            v-if="getOriginalMessage(message.ref_message_id).type === 'audio'">
                                                                            <audio controls class="w-[250px]">
                                                                                <source
                                                                                    :src="getOriginalMessage(message.ref_message_id).asset_url"
                                                                                    type="audio/mpeg">
                                                                            </audio>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <!-- Conditional rendering for different message types -->
                                                        <template v-if="message.type === 'interactive'">
                                                            <p class="text-gray-800 dark:text-white text-sm"
                                                                v-html="highlightSearch(message.message)"></p>
                                                        </template>

                                                        <template v-if="message.type === 'text'">
                                                            <p class="text-gray-800 dark:text-white text-sm" v-html="formatMessage(message.message)"></p>
                                                        </template>

                                                        <template v-else-if="message.type === 'order'">
                                                            <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-3 shadow-sm">
                                                                <div class="flex items-start gap-3">
                                                                    <!-- left: Cart Icon -->
                                                                    <div class="tw-flex tw-items-center padding-10 tw-justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                                                                        <i class="fas fa-shopping-cart"></i>
                                                                    </div>
                                                                    <!-- right: order details -->
                                                                    <div class="flex-1">
                                                                        <div class="flex items-center mb-2">
                                                                            <strong class="text-green-700 dark:text-gray-200 text-sm">
                                                                                <?= _l('order_received') ?>
                                                                            </strong>
                                                                        </div>
                                                                        <div class="text-xs text-gray-700 dark:text-gray-200 space-y-1">
                                                                            <div 
                                                                                v-for="(item, index) in parsedProducts(message.message)" 
                                                                                :key="index"
                                                                                class="flex justify-between"
                                                                            >
                                                                                <span class="truncate pr-2">
                                                                                    {{ item.name }} ({{item.qty}}×{{item.price}})
                                                                                </span>

                                                                                <span class="whitespace-nowrap tw-font-bold">
                                                                                    {{ item.total }}
                                                                                </span>
                                                                            </div>

                                                                        </div>
                                                                        <div class="border-t border-gray-300 my-2"></div>
                                                                        <div class="flex justify-between text-sm font-semibold text-gray-800 dark:text-white tw-mt-0.5">
                                                                            <span><?= _l('total') ?>:</span>
                                                                            <span>{{ calculateTotal(message.message) }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </template>

                                                        <template v-if="message.type === 'button'">
                                                            <p class="text-gray-800 dark:text-white text-sm"
                                                                v-html="highlightSearch(message.message)"></p>
                                                        </template>
                                                        <template v-else-if="message.type === 'image'">
                                                            <a :href="message.asset_url" data-lightbox="image-group"
                                                                target="_blank">
                                                                <img :src="message.asset_url" alt="Image"
                                                                    class="rounded-lg max-w-xs max-h-28">
                                                            </a>
                                                            <p class="text-gray-600 text-xs mt-2 dark:text-gray-200"
                                                                v-if="message.caption">{{ message.caption }}</p>
                                                        </template>

                                                        <template v-else-if="message.type === 'video'">
                                                            <video :src="message.asset_url" controls
                                                                class="rounded-lg max-w-xs max-h-28"></video>
                                                            <p class="text-gray-600 text-xs mt-2 dark:text-gray-200"
                                                                v-if="message.message">{{ message.message }}</p>
                                                        </template>
                                                        <template v-else-if="message.type === 'sticker'">
                                                            <div class="flex justify-center">
                                                                <img :src="message.asset_url || message.url"
                                                                    alt="Sticker"
                                                                    class="rounded-lg max-w-[180px] max-h-[180px] object-contain">
                                                            </div>
                                                        </template>
                                                        <template v-else-if="message.type === 'unsupported'">
                                                            <div class="max-w-xs rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 px-3 py-2 text-amber-900 dark:text-amber-100">
                                                                <div class="flex items-center gap-2">
                                                                    <i class="fa fa-triangle-exclamation"></i>
                                                                    <span class="text-sm font-semibold"><?php echo _l('unsupported'); ?></span>
                                                                </div>
                                                                <p class="mt-1 text-xs break-words">{{ message.message }}</p>
                                                            </div>
                                                        </template>

                                                        <template v-else-if="message.type === 'document'">
                                                            <a :href="message.asset_url" target="_blank"
                                                                class="text-blue-500 underline "><?php echo _l('download_document'); ?></a>
                                                        </template>

                                                        <template v-else-if="message.type === 'audio'">
                                                            <audio controls class="w-[300px]">
                                                                <source :src="message.asset_url" type="audio/mpeg">
                                                            </audio>
                                                            <p class="text-gray-600 text-xs mt-2 dark:text-gray-200"
                                                                v-if="message.message">{{ message.message }}</p>
                                                        </template>
                                                        <template v-else-if="message.type === 'contact' || message.type === 'contacts' || getContactDisplay(message.message).label">
                                                            <div class="w-[320px] max-w-full rounded-xl border border-sky-200 bg-sky-50/80 dark:bg-sky-900/20 dark:border-sky-800 px-4 py-3">
                                                                <div class="flex items-start gap-3">
                                                                    <div class="tw-flex tw-items-center padding-10 tw-justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                                                                        <i class="fa fa-phone text-base "></i>
                                                                    </div>
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="text-sm font-semibold text-slate-900 dark:text-white break-words">
                                                                            {{ getContactDisplay(message.message).label || '<?php echo _l('contact'); ?>' }}
                                                                        </div>
                                                                        <button v-if="getContactDisplay(message.message).phone" type="button"
                                                                            class="mt-1 text-sm text-sky-700 dark:text-sky-300 decoration-dotted decoration-sky-500 underline-offset-2 break-all text-left hover:text-sky-900 dark:hover:text-sky-100"
                                                                            :title="getContactDisplay(message.message).phone || getContactDisplay(message.message).label"
                                                                            @click="copyContactNumber(getContactDisplay(message.message).phone)">
                                                                            {{ getContactDisplay(message.message).phone }}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template v-else-if="message.type === 'location'">
                                                            <div :id="'map-location-' + index"
                                                                class="text-gray-600 text-xs mt-2 w-[300px] dark:text-gray-200 z-10"
                                                                style="height:200px">
                                                            </div>
                                                            <a class="btn btn-sm btn-primary mt-2"
                                                                :href="getGoogleMapsUrl(message.message)"
                                                                target="_blank">
                                                                View on Google Maps
                                                            </a>
                                                        </template>

                                                        <!-- Message Timestamp and Status -->
                                                        <div
                                                            class="flex justify-between items-center gap-4 mt-2 text-xs text-gray-600 dark:text-gray-200">
                                                            <div class="flex items-center gap-1">
                                                                <div v-if="getMessageReactions(message.message_id).length"
                                                                    class="inline-flex items-center gap-1 rounded-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-2 py-1 shadow-sm">
                                                                    <span v-for="reaction in getMessageReactions(message.message_id)"
                                                                        :key="reaction.id"
                                                                        class="inline-flex items-center justify-center text-sm leading-none">
                                                                        {{ reaction.message }}
                                                                    </span>
                                                                </div>
                                                                <span>{{ wb_getTime(message.time_sent) }}</span>
                                                            </div>
                                                            <div>
                                                                <span v-on:click="replyToMessage(message)"
                                                                    class="cursor-pointer">
                                                                    <i class="fa-solid fa-reply"></i>
                                                                </span>
                                                                <span
                                                                    v-if="message.sender_id === wb_selectedinteraction.wa_no"
                                                                    class="ml-2">
                                                                    <i v-if="message.status === 'sent'"
                                                                        class="fa fa-check text-gray-500 dark:text-white"
                                                                        title="Sent"></i>
                                                                    <i v-else-if="message.status === 'delivered'"
                                                                        class="fa fa-check-double text-gray-500 dark:text-white"
                                                                        title="Delivered"></i>
                                                                    <i v-else-if="message.status === 'read'"
                                                                        class="fa fa-check-double text-cyan-500"
                                                                        title="Read"></i>
                                                                    <i v-else-if="message.status === 'failed'"
                                                                        class="fa fa-exclamation-circle text-red-500"
                                                                        title="Failed"></i>
                                                                    <i v-else-if="message.status === 'deleted'"
                                                                        class="fa fa-trash text-red-500"
                                                                        title="Deleted"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="replyingToMessage" class="flex justify-center w-full" v-cloak>
                                    <div class=" w-11/12 bg-[#F0F2F5] dark:bg-gray-500 rounded-lg ">
                                        <div
                                            class="bg-white dark:bg-gray-500 w-full max-w-full p-3 rounded-lg shadow-lg flex justify-between items-center mb-1">
                                            <div class="flex flex-col gap-2">
                                                <span
                                                    class="text-gray-400 font-normal dark:text-gray-300"><?php echo _l('replying_to'); ?></span>
                                                <span class="text-gray-800 font-semibold dark:text-gray-200"
                                                    v-html="replyingToMessage.message"></span>
                                                <div v-if="replyingToMessage.type === 'contact' || replyingToMessage.type === 'contacts' || getContactDisplay(replyingToMessage.message).label">
                                                    <div class="w-full max-w-xs rounded-xl border border-sky-200 bg-sky-50/80 dark:bg-sky-900/20 dark:border-sky-800 px-3 py-3 shadow-sm">
                                                        <div class="flex items-start gap-3">
                                                            <div class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-sky-600 text-white shadow-sm">
                                                                <i class="fa fa-address-card text-sm"></i>
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <div class="text-sm font-semibold text-slate-900 dark:text-white break-words">
                                                                    {{ getContactDisplay(replyingToMessage.message).label || '<?php echo _l('contact'); ?>' }}
                                                                </div>
                                                                <button v-if="getContactDisplay(replyingToMessage.message).phone" type="button"
                                                                    class="mt-1 text-sm text-sky-700 dark:text-sky-300 underline decoration-dotted decoration-sky-500 underline-offset-2 break-all text-left hover:text-sky-900 dark:hover:text-sky-100"
                                                                    :title="getContactDisplay(replyingToMessage.message).phone || getContactDisplay(replyingToMessage.message).label"
                                                                    @click="copyContactNumber(getContactDisplay(replyingToMessage.message).phone)">
                                                                    {{ getContactDisplay(replyingToMessage.message).phone }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-else-if="replyingToMessage.asset_url">
                                                    <template v-if="replyingToMessage.type === 'image'">
                                                        <img :src="replyingToMessage.asset_url"
                                                            class="rounded-lg max-w-xs max-h-28" alt="Image">
                                                    </template>
                                                    <template v-if="replyingToMessage.type === 'sticker'">
                                                        <img :src="replyingToMessage.asset_url"
                                                            class="rounded-lg max-w-xs max-h-28 object-contain" alt="Sticker">
                                                    </template>
                                                    <template v-if="replyingToMessage.type === 'video'">
                                                        <video :src="replyingToMessage.asset_url" controls
                                                            class="rounded-lg max-w-xs max-h-28"></video>
                                                    </template>
                                                    <template v-if="replyingToMessage.type === 'document'">
                                                        <a :href="replyingToMessage.asset_url" target="_blank"
                                                            class="text-blue-500 underline"><?php echo _l('download_document'); ?></a>
                                                    </template>
                                                    <template v-if="replyingToMessage.type === 'audio'">
                                                        <audio controls class="w-[250px]">
                                                            <source :src="replyingToMessage.asset_url" type="audio/mpeg">
                                                        </audio>
                                                    </template>
                                                </div>
                                            </div>
                                            <button v-on:click="clearReply">
                                                <i class="fa-regular fa-2xl fa-circle-xmark"></i>
                                            </button>
                                        </div>
                                        <ul v-if="showQuickReplies"
                                            class="flex-grow bg-white shadow-md rounded-lg mt-2 p-2">
                                            <li v-for="(reply, index) in filteredQuickReplies" :key="index"
                                                v-on:click="selectQuickReply(index)" :class="{
                                                    'bg-blue-100 text-blue-900': index === quickReplyIndex,
                                                    'cursor-pointer rounded-md p-2 transition-all duration-200 ease-in-out': true
                                                    }">
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview section Start -->
                            <div class="relative" v-cloak>
                                <div class="absolute bottom-[-15px] " v-cloak>
                                    <div v-if="wb_imageAttachment || wb_videoAttachment || wb_documentAttachment"
                                        class="flex flex-wrap gap-4 p-4">
                                        <!-- Image Attachment -->
                                        <div v-if="wb_imageAttachment"
                                            class="relative flex flex-col items-center py-6 px-4 bg-[#F0F2F5] dark:bg-gray-600 border border-gray-300 rounded-lg shadow-lg max-w-[250px] z-10">
                                            <!-- Preview Text -->
                                            <span
                                                class="text-xs font-semibold text-gray-500 dark:text-gray-200 mb-2"><?php echo _l('preview'); ?></span>
                                            <button v-on:click="wb_removeImageAttachment"
                                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500 mt-1 focus:outline-none">
                                                <i class="fa fa-times fa-lg"></i>
                                            </button>
                                            <img :src="wb_imagePreview" alt="Selected Image"
                                                class="w-full h-28 object-cover rounded-md mb-3 shadow-sm" />
                                            <span
                                                class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200 truncate w-full text-center">{{
                                                wb_imageAttachment.name }}</span>
                                        </div>
                                        <!-- Video Attachment -->
                                        <div v-if="wb_videoAttachment"
                                            class="relative z-10 flex flex-col items-center py-6 px-4 bg-[#F0F2F5] dark:bg-gray-600 border border-gray-300 rounded-lg shadow-md max-w-[280px]">
                                            <!-- Preview Text -->
                                            <span
                                                class="text-xs font-semibold text-gray-500 dark:text-gray-200 mb-2"><?php echo _l('preview'); ?></span>
                                            <button v-on:click="wb_removeVideoAttachment"
                                                class="absolute top-2 right-2 text-red-500 hover:text-red-700 focus:outline-none">
                                                <i class="fa fa-times "></i>
                                            </button>
                                            <video :src="wb_videoPreview" controls
                                                class="w-full object-cover rounded-md"></video>
                                            <span
                                                class="mt-2 text-sm dark:text-gray-200 text-gray-700 truncate w-full text-center">{{
                                                wb_videoAttachment.name }}</span>
                                        </div>
                                        <!-- Document Attachment -->
                                        <div v-if="wb_documentAttachment"
                                            class="relative flex flex-col items-center p-2 bg-[#F0F2F5] dark:bg-gray-600 border border-gray-300 rounded-lg shadow-md max-w-[250px] min-w-[200px] z-10">
                                            <!-- Preview Text -->
                                            <span
                                                class="text-xs font-semibold text-gray-500 dark:text-gray-200 mb-2"><?php echo _l('preview'); ?></span>
                                            <button v-on:click="wb_removeDocumentAttachment"
                                                class="absolute top-2 right-2 text-red-500 hover:text-red-700 focus:outline-none">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <i class="fa fa-file text-gray-600  text-4xl"></i>
                                            <span
                                                class="mt-2 text-sm text-gray-700 truncate w-full text-center dark:text-gray-200">{{
                                                wb_documentAttachment.name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Preview section End -->
                            </div>

                            <!-- Internal note section -->
                            <div v-if="wb_showNoteInput && wb_selectedinteraction"
                                class="w-full px-2 py-2 bg-yellow-50 dark:bg-yellow-900/20 border-t border-yellow-200 dark:border-yellow-800 z-10">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-sticky-note text-yellow-500"></i>
                                    <input type="text" v-model="wb_internalNote"
                                        class="form-control form-control-sm flex-1"
                                        placeholder="<?php echo _l('internal_note_placeholder'); ?>"
                                        @keydown.enter.prevent="wb_sendInternalNote">
                                    <button type="button" class="btn btn-warning" @click="wb_sendInternalNote">
                                        <i class="fa fa-save"></i>
                                    </button>
                                    <button type="button" class="btn btn-default" @click="wb_showNoteInput = false">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- reply section start-->
                            <div v-if="wb_selectedinteraction && wb_selectedinteraction.messages && is_pemission_replay_input"
                                class="right-bottom w-full top-full sticky flex justify-between items-center px-2 py-2 rounded dark:bg-[#1E293B] bg-white z-10"
                                v-cloak>
                                <form v-on:submit.prevent="wb_sendMessage" class="flex flex-col w-full relative"
                                    v-cloak>
                                    <!-- Input Field at the Top -->
                                    <div class="w-full dark:bg-gray-600  bg-gray-100 rounded-lg px-4 py-1 text-sm">
                                        <textarea v-model="wb_newMessage" ref="inputField"
                                            placeholder="<?= _l('type_your_message'); ?>"
                                            class="mentionable w-full bg-transparent focus:outline-none px-2 py-2 h-[40px] dark:text-white resize-none text-sm placeholder-center"
                                            id="wb_newMessage" @keydown="handleKeyPress"></textarea>
                                    </div>
                                    <div class="flex justify-between items-center space-x-4" v-cloak>
                                        <!-- Left Side Icon Column -->
                                        <div class="flex space-x-2 items-center">
                                            <!-- OpenAI Button (if enabled) -->
                                            <?php if (get_option('enable_wb_openai')) { ?>
                                                <div class="dropup" tabindex="0" data-toggle="tooltip"
                                                    data-title='<?php echo _l('ai_prompt_note'); ?>'>
                                                    <button class="btn dropdown-toggle p-2"
                                                        :class="{ 'disabled': !isButtonEnabled }" type="button"
                                                        id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i
                                                            class="fa-solid fa-robot text-violet-500 dark:text-cyan-400 text-xl"></i>
                                                    </button>

                                                    <ul class="dropdown-menu w-[300px] dark:bg-gray-600"
                                                        aria-labelledby="dropdownMenu2">
                                                        <!-- Menu Items -->
                                                        <li class="dropdown-header">
                                                            <span class="tw-mr-1"><i
                                                                    class="fa-solid fa-robot text-sky-500 dark:text-cyan-400"></i></span><span
                                                                class="dark:text-gray-200"><?= _l('ai_prompt'); ?></span>
                                                        </li>
                                                        <li role="separator" class="divider dark:bg-gray-400"></li>
                                                        <li class="dropdown dropdown-submenu">
                                                            <a href="javascript:;" class="dark:text-gray-200"><i
                                                                    class="fa-solid fa-headset text-sky-500 dark:text-cyan-400 mr-2"></i>Change
                                                                Tone</span></a>
                                                            <ul class="dropdown-menu dark:bg-gray-600"
                                                                style="top: 35px;margin-top: -140px;">
                                                                <li
                                                                    v-on:click="wb_handleItemClick('Change Tone', '<?= _l('professional'); ?>')">
                                                                    <a href="javascript:;"
                                                                        class="dark:text-gray-200"><?= _l('professional'); ?></a>
                                                                </li>
                                                                <li
                                                                    v-on:click="wb_handleItemClick('Change Tone', '<?= _l('friendly'); ?>')">
                                                                    <a href="javascript:;"
                                                                        class="dark:text-gray-200"><?= _l('friendly'); ?></a>
                                                                </li>
                                                                <li
                                                                    v-on:click="wb_handleItemClick('Change Tone', '<?= _l('empathetic'); ?>')">
                                                                    <a href="javascript:;"
                                                                        class="dark:text-gray-200"><?= _l('empathetic'); ?></a>
                                                                </li>
                                                                <li
                                                                    v-on:click="wb_handleItemClick('Change Tone', '<?= _l('straightforward'); ?>')">
                                                                    <a href="javascript:;"
                                                                        class="dark:text-gray-200"><?= _l('straightforward'); ?></a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                        <li class="dropdown-submenu">
                                                            <a href="javascript:;" class="dark:text-gray-200">
                                                                <i
                                                                    class="fa-solid fa-language text-sky-500 dark:text-cyan-400 tw-mr-2"></i>
                                                                Translate
                                                            </a>
                                                            <ul class="dropdown-menu dropdown-menu dark:bg-gray-600"
                                                                style="top: 35px; margin-top: -350px;">
                                                                <li>
                                                                    <input type="text" class="form-control dark:bg-gray-700"
                                                                        style="border-radius: 25px;" v-model="searchQuery"
                                                                        placeholder="<?= _l('search_language'); ?>" />
                                                                </li>
                                                                <li v-for="lang in filteredLanguages" :key="lang">
                                                                    <a href="javascript:;" class="dark:text-gray-200"
                                                                        v-on:click="wb_handleItemClick('Translate', lang)">
                                                                        {{ ucfirst(lang) }}
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                        <li v-on:click="wb_handleItemClick('Fix Spelling & Grammar')">
                                                            <a href="javascript:;" class="dark:text-gray-200"><i
                                                                    class="fa-solid fa-check text-sky-500 dark:text-cyan-400 tw-mr-2"></i>Fix
                                                                Spelling & Grammar</a>
                                                        </li>
                                                        <li v-on:click="wb_handleItemClick('Simplify Language')">
                                                            <a href="javascript:;" class="dark:text-gray-200"><i
                                                                    class="fa-solid fa-virus text-sky-500 dark:text-cyan-400 tw-mr-2"></i>Simplify
                                                                Language</a>
                                                        </li>
                                                        <li class="dropdown dropdown-submenu"
                                                            v-if="customPrompts.length > 0">
                                                            <a href="javascript:;" class="dark:text-gray-200"><i
                                                                    class="fa-solid fa-reply text-sky-500 dark:text-cyan-400 mr-2"></i>Custom
                                                                Prompt</a>
                                                            <ul class="dropdown-menu dark:bg-gray-600"
                                                                style="top: 35px;margin-top: -80px;">
                                                                <li v-for="(prompt, index) in customPrompts" :key="index"
                                                                    v-if="shouldDisplayPrompt(prompt)"
                                                                    v-on:click="wb_handleItemClick('Custom Prompt', prompt.action)">
                                                                    <a href="javascript:;" class="dark:text-gray-200">{{
                                                                        prompt.label }}</a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            <?php } ?>
                                            <!-- Emoji Button -->
                                            <button v-on:click="toggleEmojiPicker" id="emoji_btn" type="button"
                                                class="flex justify-center items-center cursor-pointer dark:text-gray-200 hover:text-blue-900">
                                                <i class="fa-regular fa-face-grin fa-xl" data-toggle="tooltip"
                                                    data-title="<?= _l('emojis'); ?>" data-placement="top"></i>
                                            </button>

                                            <!-- Attachment Button -->
                                            <button v-on:click="toggleAttachmentOptions" type="button"
                                                class="flex items-center justify-center p-2 dark:text-gray-200 text-gray-700 hover:text-blue-900 focus:outline-none"
                                                title="<?= _l('attach_image_video_docs'); ?>" data-toggle="tooltip"
                                                data-title="<?= _l('attach_image_video_docs'); ?>" data-placement="top">
                                                <i class="fa-solid fa-paperclip fa-xl"></i>
                                            </button>
                                            <!-- Internal Note Button -->
                                            <button v-on:click="wb_showNoteInput = !wb_showNoteInput" type="button"
                                                class="flex items-center justify-center p-2 dark:text-gray-200 text-gray-700 hover:text-yellow-600 focus:outline-none"
                                                :class="{'text-yellow-500': wb_showNoteInput}"
                                                title="<?= _l('internal_note'); ?>" data-toggle="tooltip"
                                                data-title="<?= _l('internal_note'); ?>" data-placement="top">
                                                <i class="fa-solid fa-sticky-note fa-xl"></i>
                                            </button>

                                            <!-- Improved Canned Replies Dropdown -->
                                            <div v-if="(cannedRepliesVisible && cannedReplies.length) || (slashCommandActive && slashFilteredReplies.length)"
                                                class="absolute bottom-[96px] overflow-y-auto left-[120px] bg-white dark:bg-gray-600 rounded-lg shadow-lg flex flex-col space-y-2 p-2 max-h-[400px] mobile-canned"
                                                :id="slashCommandActive ? 'slash_command_replies' : 'canned_replies'"
                                                v-cloak style="width: 65%;">

                                                <!-- Header with title -->
                                                <div
                                                    class="bg-blue-500 dark:bg-cyan-500 text-white font-bold px-4 py-1 rounded flex justify-between items-center">
                                                    <div>
                                                        <i class="fa-regular fa-message mr-2"></i>
                                                        {{ slashCommandActive ? `Quick replies matching:
                                                        "${slashCommandQuery}"` : `<?= _l('canned_replies'); ?>` }}
                                                    </div>
                                                    <span
                                                        class="text-xs bg-blue-400 dark:bg-cyan-400 rounded-full px-2 py-1">
                                                        {{ (slashCommandActive ? slashFilteredReplies :
                                                        cannedReplies).filter(r => shoud_wb_cannedReplyData(r)).length
                                                        }} <?= _l('found'); ?>
                                                    </span>
                                                </div>

                                                <!-- Empty state when no results found -->
                                                <div v-if="(slashCommandActive ? slashFilteredReplies : cannedReplies).filter(r => shoud_wb_cannedReplyData(r)).length === 0"
                                                    class="flex flex-col items-center justify-center p-4 text-gray-500 dark:text-gray-400">
                                                    <i class="fa-regular fa-face-frown text-2xl mb-2"></i>
                                                    <p>No matching replies found</p>
                                                </div>

                                                <!-- List of replies with keyboard navigation highlight -->
                                                <li v-for="(reply, index) in (slashCommandActive ? slashFilteredReplies : cannedReplies)"
                                                    v-if="shoud_wb_cannedReplyData(reply)" :key="reply.title"
                                                    class="relative flex flex-col px-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer rounded-md mb-2 shadow-sm transition-colors duration-150"
                                                    :class="{
            'custom-border': darkMode,
            'bg-blue-100 dark:bg-blue-800 border-blue-300 dark:border-blue-600': (slashCommandActive && index === selectedReplyIndex)
        }" @click="slashCommandActive ? selectSlashReply(reply) : addToMessage(reply)" ref="replyItems">

                                                    <!-- Title and visibility badge -->
                                                    <div class="flex justify-between items-center">
                                                        <div
                                                            class="font-semibold text-gray-800 dark:text-white truncate w-[385px]">
                                                            {{ reply.title }}
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ reply.description.length }} chars
                                                            </span>
                                                            <span v-if="reply.is_public === '1'"
                                                                class="bg-green-200 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                                                <?= _l('public'); ?>
                                                            </span>
                                                            <span v-else
                                                                class="bg-orange-200 text-orange-800 text-xs font-semibold px-2 py-1 rounded">
                                                                <?= _l('private'); ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Description text -->
                                                    <div class="text-gray-600 dark:text-white text-sm truncate">
                                                        {{ reply.description }}
                                                    </div>
                                                </li>
                                            </div>

                                            <!-- Canned Button -->
                                            <button v-if="cannedReplies.length > 0" v-on:click="toggleCannedReplies"
                                                ref="cannedRepliesDropdown" type="button"
                                                class="flex items-center justify-center p-2 text-gray-700 dark:text-gray-200 hover:text-blue-900 focus:outline-none"
                                                title="<?= _l('canned_reply'); ?>" data-toggle="tooltip"
                                                data-title="<?= _l('attach_image_video_docs'); ?>" data-placement="top">
                                                <i class="fa-regular fa-message fa-lg"></i>
                                            </button>
                                            <!-- Notes Button -->
                                            <button v-on:click='wb_noteModal_Open' type="button"
                                                class="flex items-center justify-center p-2 text-gray-700 dark:text-gray-200 hover:text-blue-900 focus:outline-none"
                                                title="<?= _l('add_notes') ?>" data-toggle="tooltip"
                                                data-placement="top">
                                                <i class="fa-xl fa-note-sticky fa-regular"></i>
                                            </button>
                                            <!-- Campaigns button -->
                                            <button v-on:click="wb_TemplateModal" type="button"
                                                class="flex items-center justify-center p-2 text-gray-700 dark:text-gray-200 hover:text-blue-900 focus:outline-none"
                                                title="<?= _l('send_campaign') ?>" data-toggle="tooltip"
                                                data-placement="top">
                                                <i class="fa-envelope fa-regular fa-xl"></i>
                                            </button>
                                            <!-- Recording Button -->
                                            <button v-on:click="wb_toggleRecording" type="button"
                                                class="flex items-center justify-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900 focus:outline-none"
                                                title="<?= _l('record_audio'); ?>">
                                                <span v-if="!wb_recording" class="fa fa-microphone text-xl"
                                                    aria-hidden="true" data-toggle="tooltip"
                                                    data-title="<?= _l('record_audio'); ?>" data-placement="top"></span>
                                                <span v-else class="fa fa-stop text-xl" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                        <div class="flex justify-end items-center gap-4">
                                            <div
                                                class="text-sm text-gray-500 dark:text-gray-200 font-semibold mainsidebar-class">
                                                <?php echo _l('use_@_to_add_merge_fields'); ?>
                                            </div>
                                            <!-- Send Button (conditionally visible) -->
                                            <button v-if="wb_showSendButton || wb_audioBlob" type="submit"
                                                class="flex items-center justify-center p-2 bg-green-500 rounded-full focus:outline-none">
                                                <i class="fa fa-paper-plane text-white dark:text-gray-900"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <div class="absolute bottom-[100px]" id="all_atech" v-cloak>
                                            <!-- Attachment Options Dropdown (conditionally visible) -->
                                            <div v-if="showAttachmentOptions"
                                                class="flex flex-col gap-2 text-nowrap bg-[#F0F2F5] dark:bg-gray-600 shadow-lg rounded-lg p-2">
                                                <input type="file" id="imageAttachmentInput" ref="imageAttachmentInput"
                                                    v-on:change="wb_handleImageAttachmentChange"
                                                    accept="<?= wb_get_allowed_extension()['image']['extension']; ?>"
                                                    class="hidden">
                                                <label for="imageAttachmentInput"
                                                    class="cursor-pointer flex items-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900">
                                                    <i class="fa-regular text-blue-500 fa fa-image mr-2 fa-lg"
                                                        aria-hidden="true"></i><span><?= _l('send_image'); ?></span>
                                                </label>

                                                <input type="file" id="videoAttachmentInput" ref="videoAttachmentInput"
                                                    v-on:change="wb_handleVideoAttachmentChange"
                                                    accept="<?= wb_get_allowed_extension()['video']['extension']; ?>"
                                                    class="hidden">
                                                <label for="videoAttachmentInput"
                                                    class="cursor-pointer flex items-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900">
                                                    <i class="fa fa-video text-green-500 mr-2 fa-lg"
                                                        aria-hidden="true"></i><span><?= _l('send_video'); ?></span>
                                                </label>

                                                <input type="file" id="documentAttachmentInput"
                                                    ref="documentAttachmentInput"
                                                    v-on:change="wb_handleDocumentAttachmentChange"
                                                    accept="<?= wb_get_allowed_extension()['document']['extension']; ?>"
                                                    class="hidden">
                                                <label for="documentAttachmentInput"
                                                    class="cursor-pointer flex items-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900">
                                                    <i class="fa-regular text-yellow-500 fa fa-file mr-2 fa-lg"
                                                        aria-hidden="true"></i><span><?= _l('send_document'); ?></span>
                                                </label>
                                                <lable for="location" v-on:click="mapfixsize"
                                                    class="cursor-pointer flex items-center tw-gap-2 p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900"
                                                    data-toggle="modal" data-target="#locationPickerModal">
                                                    <i class="fa-solid fa-lg fa-location-dot text-blue-500"></i><span
                                                        class="font-semibold"><?= _l('send_location'); ?></span>
                                                </lable>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="emoji-picker-container" ref="emojiPickerContainer" v-cloak></div>
                                    <input type="hidden" name="rel_type" id="rel_type" value="">
                                </form>
                            </div>
                            <!-- Location Picker Modal -->
                            <div class="modal fade" id="locationPickerModal" tabindex="-1" role="dialog"
                                aria-labelledby="locationPickerModalLabel">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="locationPickerModalLabel">
                                                <?= _l('send_location'); ?>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label
                                                            for="locationSearch"><?= _l('search_for_a_place'); ?></label>
                                                        <div class="tw-flex tw-gap-1">
                                                            <input type="text" class="form-control" id="locationSearch"
                                                                placeholder="Search for a location"
                                                                v-model="locationSearch" @keyup.enter="searchLocation">
                                                            <button class="btn btn-primary" type="button"
                                                                @click="searchLocation">
                                                                <i class="fa fa-search"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <ul v-if="searchResults.length" class="list-group mt-1"
                                                        style="max-height: 200px; overflow-y: auto;">
                                                        <li class="list-group-item" v-for="result in searchResults"
                                                            :key="result.place_id" @click="selectSearchResult(result)"
                                                            style="cursor: pointer;">
                                                            {{ result.display_name }}
                                                        </li>
                                                    </ul>

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="locationAddress"><?= _l('address_optional'); ?></label>
                                                        <input type="text" class="form-control" id="locationAddress"
                                                            placeholder="Enter address" v-model="locationAddress" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="locationLat"><?= _l('latitude'); ?></label>
                                                        <input type="number" step="0.000001" class="form-control"
                                                            id="locationLat" placeholder="Latitude" v-model="lat"
                                                            @change="updateMapFromLatLng" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="locationLng"><?= _l('longitude'); ?></label>
                                                        <input type="number" step="0.000001" class="form-control"
                                                            id="locationLng" placeholder="Longitude" v-model="lng"
                                                            @change="updateMapFromLatLng" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div :id="mapId" class="w-full" style="height: 300px;"></div>

                                                    <p class="text-muted mt-2">
                                                        <?= _l('drag_the_maker_to_set_the_location'); ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <button type="button" class="btn btn-primary"
                                                        @click="useCurrentLocation" id="useCurrentLocation">
                                                        <i class="fa fa-crosshairs"></i><?= _l('use_my_location'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-primary" id="sendLocationBtn"
                                                v-on:click="wb_sendMessage"><?= _l('send_location'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Location Picker Modalend -->
                            <!-- reply section end-->
                            <!-- Chat-section End -->
                        </div>
                    </template>
                </div>
                <!-- Main content end-->
            </div>
        </div>
        <!-- Template modal -->
        <?php echo form_open_multipart(admin_url('whatsbot/send_chat_campaign'), ['id' => 'chat_form']); ?>
        <div class="modal fade" id="template-modal" role="dialog">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo _l('send_new_campaign'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <?php echo render_select('template_id', $templates, ['id', 'template_name', 'language'], 'template', ''); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="variableDetails hide">
                                <div class="col-md-4">
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <div class="tw-flex tw-justify-between tw-items-center">
                                                <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin">
                                                    <?php echo _l('variables'); ?>
                                                </h4>
                                                <span class="text-muted"><?php echo _l('merge_field_note'); ?></span>
                                            </div>
                                            <div class="clearfix"></div>
                                            <hr class="hr-panel-separator">
                                            <div class="variables"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="row" id="preview_message">

                                        <div class="panel_s">
                                            <div class="panel-body">
                                                <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin">
                                                    <?php echo _l('preview'); ?>
                                                </h4>
                                                <div class="clearfix"></div>
                                                <hr class="hr-panel-separator">
                                                <div class="padding"
                                                    style='background: url(" <?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/bg.png'); ?>");'>
                                                    <div class="wtc_panel previewImage">
                                                    </div>
                                                    <div class="panel_s no-margin">
                                                        <div class="panel-body previewmsg"></div>
                                                    </div>
                                                    <div class="previewBtn">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal"><?php echo _l('close_btn'); ?></button>
                        <button v-on:click="wb_campaignsSubmitForm"
                            class="btn btn-primary"><?php echo _l('send_btn'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<div id="contact_data"></div>
<?php init_tail(); ?>
<?php $this->load->view('admin/clients/client_js'); ?>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/vue.min.js') . '?v=' . $module_version; ?>"></script>
<script
    src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/axios.min.js') . '?v=' . $module_version; ?>"></script>
<script
    src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/recorder-core.js') . '?v=' . $module_version; ?>"></script>
<script
    src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/purify.min.js') . '?v=' . $module_version; ?>"></script>
<script
    src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/mp3-engine.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/mp3.js') . '?v=' . $module_version; ?>"></script>
<script
    src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/emoji-mart.min.js') . '?v=' . $module_version; ?>"></script>
<script
    src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/location/leaflet.js') . '?v=' . $module_version; ?>"></script>
<script
    src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/location/leaflet_fullscreen.js') . '?v=' . $module_version; ?>"></script>

<script>
    "use strict";
    $(document).on('click', '.hideMessage', function() {
        $(this).parent().addClass('hide');
    });

    new Vue({
        el: '#app',
        data() {
            return {
                interactions: [],
                previousCounts: {},
                wb_selectedinteractionIndex: null,
                wb_selectedinteractionDisplayedIndex: null,
                wb_selectedinteraction: null,
                wb_selectedinteractionMobNo: null,
                wb_selectedinteractionSenderNo: null,
                wb_newMessage: '',
                wb_internalNote: '',
                wb_showNoteInput: false,
                wb_agentId: '',
                wb_selectedStaffId: '',
                wb_selectedinteractionId: null,
                wb_imageAttachment: null,
                wb_videoAttachment: null,
                wb_documentAttachment: null,
                wb_imagePreview: '',
                wb_videoPreview: '',
                wb_recording: false,
                wb_audioBlob: null,
                wb_recordedAudio: null,
                errorMessage: '',
                sameNumErrorMessage: '',
                wb_searchText: '',
                wb_login_staff_id: '<?= get_staff_user_id(); ?>',
                wb_selectedWaNo: '<?= get_option("wac_default_phone_number"); ?>', // Define wb_selectedWaNo variable
                wb_default_number: '<?= get_option("wac_default_phone_number"); ?>',
                wb_filteredInteractions: [], // Define wb_filteredInteractions to store filtered interactions
                wb_displayedInteractions: [],
                wb_showEmojiPicker: false,
                isLoading: false,
                showQuickReplies: false,
                filteredQuickReplies: [],
                languages: <?= json_encode(config_item('languages')); ?>,
                searchQuery: '',
                showAttachmentOptions: false,
                replyingToMessage: null,
                cannedReplies: [],
                customPrompts: [],
                cannedRepliesVisible: false,
                has_pemission_view_canned_reply: '<?= staff_can('view', 'wtc_canned_reply'); ?>',
                has_pemission_view_ai_prompts: '<?= staff_can('view', 'wtc_ai_prompts'); ?>',
                //new_chat
                isShowChatMenu: false,
                isShowUserChat: false,
                darkMode: false,
                selectedTab: 'searching',
                wb_reltype_filter: '',
                wb_contact_groups: '',
                wb_lead_sources: '',
                wb_lead_status: '',
                // custom
                wb_labels_filter: '',
                wb_agents_filter: '',
                noResultsMessage: '',
                badgeSourceName: '',
                badgeStatusName: '',
                badgeStatusColor: '',
                labelName: [],
                labelColor: [],
                badgeGroupNames: [],
                wb_requireData: [],
                wb_pusher_api_key: '<?= get_option('pusher_app_key'); ?>',
                wb_pusher_cluster: '<?= get_option('pusher_cluster'); ?>',
                is_pemission_replay_input: !('<?= !is_admin() && staff_can('view_own', 'wtc_chat'); ?>'),
                // 3.0.2
                wb_messagesSearch: false,
                searchMessagesText: '',
                matchedMessages: '',
                searchError: '',
                // map
                lat: null,
                lng: null,
                locationName: '',
                locationAddress: '',
                locationSearch: '',
                searchResults: [],
                mapId: 'map-' + Date.now(),
                map: null,
                marker: null,
                isSendingLocation: false,
                wb_get_chat_id: "<?= !empty($this->input->get('chat_id')) ? $this->input->get('chat_id') : 0 ?>",
                slashCommandActive: false,
                slashCommandQuery: '',
                selectedReplyIndex: -1,
            };
        },

        methods: {
            wb_campaignsSubmitForm(e) {
                e.preventDefault();
                const form = document.getElementById('chat_form');
                const interactionData = this.wb_selectedinteraction;
                const formData = new FormData(form);

                formData.append('name', `${interactionData.name}_campaign`);
                formData.append('rel_type', interactionData.type.trim());

                formData.append('sender_phone', <?= get_option('wac_phone_number_id') ?>);
                if (interactionData.type.trim() == 'leads') {
                    formData.append('lead_ids[]', [interactionData.type_id]);
                } else if (interactionData.type.trim() == 'contacts') {
                    formData.append('contact_ids[]', [interactionData.type_id]);
                }
                formData.append('send_now', true);
                $.ajax({
                    url: '<?= admin_url('whatsbot/send_chat_campaign') ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        const parsedResponse = JSON.parse(response);
                        alert_float(parsedResponse.type, parsedResponse.message);
                        $('#template-modal').modal('hide');
                    },
                    error: function(error) {
                        const errorResponse = JSON.parse(error.responseText);
                        alert_float(errorResponse.type, errorResponse.message);
                    },
                });
            },
            // Updated getChats method with async/await
            async getChats(lastChatId = 0, searchText = '', replace = false) {
                try {
                    let url = `<?= admin_url('whatsbot/chat_data'); ?>/${lastChatId}`;
                    const params = new URLSearchParams();
                    if (searchText) params.append('search', searchText);
                    if ([...params].length) url += '?' + params.toString();
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    const staff_id = this.wb_login_staff_id;
                    const enable_supportagent = "<?= get_option('enable_supportagent'); ?>";

                    const isAdmin = <?php echo is_admin() ? 'true' : 'false'; ?>;

                    const data = await response.json();

                    let newInteractions = data || [];

                    // --- Apply support agent filtering ---
                    if (!isAdmin && enable_supportagent == 1 && Array.isArray(newInteractions)) {
                        newInteractions = newInteractions.filter(interaction => {
                            const chatagent = interaction.agent;
                            if (chatagent) {
                                const agentIds = Array.isArray(chatagent.agent_id) ? chatagent.agent_id : [chatagent.agent_id];
                                const assignIds = Array.isArray(chatagent.assign_id) ? chatagent.assign_id : [chatagent.assign_id];
                                // Check if staff_id exists in either array
                                return agentIds.includes(staff_id) || assignIds.includes(staff_id);
                            }
                            return false;
                        });
                    }

                    // Merge with existing interactions and remove duplicates
                    if (replace) {
                        // For search: replace all interactions
                        this.interactions = newInteractions;
                    } else {
                        // For scroll: merge and deduplicate
                        this.interactions = [
                            ...this.interactions,
                            ...newInteractions
                        ];
                        this.interactions = Array.from(new Map(this.interactions.map(obj => [obj.id, obj])).values());
                    }

                    // --- Filter chat and update UI ---
                    this.wb_filterInteractions();
                    this.wb_updateSelectedInteraction();
                    setTimeout(() => {
                        this.handleLabelDisplay();
                    }, 1000);

                    return this.interactions;


                } catch (error) {
                    console.error('Error fetching messages:', error);
                    return [];
                }
            },
            // Updated getChatMessages method with async/await
            async getChatMessages(chatId, lastMessageId = 0) {
                try {
                    const url = lastMessageId > 0 ?
                        `<?= admin_url('whatsbot/chat_messages_get'); ?>/${chatId}/${lastMessageId}` :
                        `<?= admin_url('whatsbot/chat_messages_get'); ?>/${chatId}`;

                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json'

                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error fetching messages:', error);
                    return [];
                }
            },
            // sidebar chat get on scroll
            async onSidebarScroll(event) {
                const chatSidebarBox = this.$refs.chatSidebar;
                const isAtBottom = chatSidebarBox.scrollHeight - chatSidebarBox.scrollTop <= chatSidebarBox
                    .clientHeight + 1;

                if (isAtBottom && !this.hasReachedBottom) {
                    this.hasReachedBottom = true;

                    // Create loader UI and append at bottom
                    const wrapperDiv = document.createElement('div');
                    wrapperDiv.className = 'flex justify-center w-full my-2';
                    wrapperDiv.id = 'loader-wrapper';

                    const loader = document.createElement('div');
                    loader.className =
                        'text-center py-2 px-4 rounded-full bg-white dark:bg-gray-700 shadow-sm inline-flex items-center transition-all duration-300';
                    loader.id = 'scroll-loader';
                    loader.innerHTML =
                        '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite] mr-2"></span><span class="text-gray-700 dark:text-gray-200">Loading chats...</span>';

                    wrapperDiv.appendChild(loader);
                    chatSidebarBox.appendChild(wrapperDiv);

                    await this.$nextTick(); // Let Vue render loader
                    chatSidebarBox.scrollTop = chatSidebarBox.scrollHeight; // Scroll to bottom to see loader

                    try {
                        const lastChat = this.wb_displayedInteractions?.[this.wb_displayedInteractions.length - 1];
                        const lastChatId = lastChat?.interaction_id ?? lastChat?.id ?? null;
                        if (!lastChatId) {
                            console.warn('No messages to load.');
                            document.getElementById('loader-wrapper')?.remove();
                            return;
                        }
                        // Add artificial delay to show the spinner nicely (e.g., 600ms)
                        await new Promise(resolve => setTimeout(resolve, 800));
                        const searchText = this.wb_searchText.trim();
                        let isSearch = searchText != '' ? true : false;
                        const newerChats = await this.getChats(lastChatId, searchText, isSearch);
                        document.getElementById('loader-wrapper')?.remove();

                        if (newerChats.length > 0) {

                            await this.$nextTick();
                            chatSidebarBox.scrollTop = chatSidebarBox.scrollHeight; // Stay at bottom
                        } else {
                            // Show "no more messages" only once
                            const noMore = document.createElement('div');
                            noMore.className = `
                                w-full flex justify-center my-3 transition-opacity duration-300
                            `;

                            noMore.innerHTML = `
                                <div class="text-center py-1.5 px-4 rounded-full bg-white dark:bg-gray-800 text-sm text-gray-500 dark:text-gray-400 shadow-sm">
                                    No more messages
                                </div>
                            `;

                            chatSidebarBox.appendChild(noMore);

                            // Scroll to bottom to ensure message is visible
                            await this.$nextTick();
                            chatSidebarBox.scrollTop = chatSidebarBox.scrollHeight;

                            // Auto-remove after 2.5 seconds with fade out effect
                            setTimeout(() => {
                                noMore.classList.add('opacity-0');
                                setTimeout(() => noMore.remove(), 300);
                            }, 2500);

                        }
                    } catch (error) {
                        console.error('Error loading messages:', error);
                        document.getElementById('loader-wrapper')?.remove();
                    }
                }

                if (!isAtBottom && this.hasReachedBottom) {
                    this.hasReachedBottom = false;
                }
            },
            // Updated checkScrollTop method with async/await
            async checkScrollTop(chatId) {
                let chatBox = this.$refs.wb_chatContainer;
                if (chatBox.scrollTop === 0) {
                    try {
                        const oldScrollHeight = chatBox.scrollHeight;

                        // Loader UI
                        const wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'loader-wrapper';
                        wrapperDiv.id = 'loader-wrapper';

                        const loader = document.createElement('div');
                        loader.className = 'scroll-loader';
                        loader.id = 'scroll-loader';
                        loader.innerHTML =
                            '<span class="spinner"></span><span class="loader-text">Loading messages...</span>';

                        wrapperDiv.appendChild(loader);
                        chatBox.prepend(wrapperDiv);

                        const firstMessageId = this.wb_selectedinteraction?.messages?.length ?
                            this.wb_selectedinteraction.messages[0].id :
                            null;

                        if (!firstMessageId) {
                            console.warn('No messages available to load older ones.');
                            document.getElementById('loader-wrapper')?.remove();
                            return;
                        }

                        const olderMessages = await this.getChatMessages(chatId, firstMessageId);

                        document.getElementById('loader-wrapper')?.remove();
                        if (olderMessages.length > 0) {
                            const transitionContainer = document.createElement('div');
                            transitionContainer.className = 'fade-in-container';
                            transitionContainer.id = 'new-messages-container';
                            chatBox.prepend(transitionContainer);

                            this.wb_selectedinteraction.messages = [
                                ...olderMessages,
                                ...this.wb_selectedinteraction.messages
                            ];

                            await this.$nextTick();
                            chatBox.scrollTop = chatBox.scrollHeight - oldScrollHeight;

                            setTimeout(() => {
                                const container = document.getElementById('new-messages-container');
                                if (container) {
                                    container.classList.add('visible');
                                    setTimeout(() => {
                                        if (container && container.parentNode) {
                                            container.replaceWith(...container.childNodes);
                                        }
                                    }, 500);
                                }
                            }, 10);
                        } else {
                            const noMoreWrapper = document.createElement('div');
                            noMoreWrapper.className = 'info-wrapper';
                            noMoreWrapper.id = 'no-more-wrapper';

                            const noMoreElement = document.createElement('div');
                            noMoreElement.className = 'info-message';
                            noMoreElement.id = 'no-more-messages';
                            noMoreElement.innerText = 'No more messages';

                            noMoreWrapper.appendChild(noMoreElement);
                            chatBox.prepend(noMoreWrapper);

                            setTimeout(() => {
                                noMoreElement.classList.add('fade-out');
                                setTimeout(() => {
                                    document.getElementById('no-more-wrapper')?.remove();
                                }, 300);
                            }, 2000);
                        }
                    } catch (error) {
                        console.error('Error loading older messages:', error);
                        document.getElementById('loader-wrapper')?.remove();

                        const errorWrapper = document.createElement('div');
                        errorWrapper.className = 'info-wrapper';
                        errorWrapper.id = 'error-wrapper';

                        const errorElement = document.createElement('div');
                        errorElement.className = 'error-message';
                        errorElement.id = 'load-error';
                        errorElement.innerText = 'Failed to load messages';

                        errorWrapper.appendChild(errorElement);
                        chatBox.prepend(errorWrapper);

                        setTimeout(() => {
                            errorElement.classList.add('fade-out');
                            setTimeout(() => {
                                document.getElementById('error-wrapper')?.remove();
                            }, 300);
                        }, 3000);
                    }
                }
            },
            // Add method to select a reply from slash command dropdown
            selectSlashReply(reply) {
                this.wb_newMessage = reply.description;
                this.closeSlashCommand();
                this.$nextTick(() => {
                    this.$refs.inputField.focus();
                });
            },

            // Add method to close slash command dropdown
            closeSlashCommand() {
                this.slashCommandActive = false;
                this.slashCommandQuery = '';
                this.selectedReplyIndex = -1;
            },

            // Toggle canned replies visibility
            toggleCannedReplies() {
                this.cannedRepliesVisible = !this.cannedRepliesVisible;
                this.slashCommandActive = false; // Close slash command if open

                if (this.cannedRepliesVisible) {
                    // Focus on the first item when opened
                    this.$nextTick(() => {
                        const firstItem = document.querySelector('#canned_replies .grid-cols-1 > div, #canned_replies .grid-cols-2 > div');
                        if (firstItem) firstItem.focus();
                    });
                }
            },

            // Add reply to message
            addToMessage(reply) {
                this.wb_newMessage = reply.description;
                this.cannedRepliesVisible = false;

                // Show a toast confirmation
                this.showToast('Reply added to message');

                this.$nextTick(() => {
                    this.$refs.inputField.focus();
                });
            },

            // Optional: Show a toast notification
            showToast(message) {
                // You can implement a simple toast notification here
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-70 text-white px-4 py-2 rounded-full text-sm z-50';
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s';
                    setTimeout(() => document.body.removeChild(toast), 500);
                }, 1500);
            },
            scrollToSelectedItem() {
                this.$nextTick(() => {
                    const container = document.getElementById(this.slashCommandActive ? 'slash_command_replies' : 'canned_replies');
                    const selectedItem = container?.querySelector('.bg-blue-100, .dark\\:bg-blue-800');

                    if (container && selectedItem) {
                        // Get container and item positions
                        const containerRect = container.getBoundingClientRect();
                        const selectedRect = selectedItem.getBoundingClientRect();

                        // Check if the element is not visible in the container
                        if (selectedRect.bottom > containerRect.bottom || selectedRect.top < containerRect.top) {
                            // Scroll the item into view with some padding
                            selectedItem.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest'
                            });
                        }
                    }
                });
            },

            handleKeyPress(event) {
                // Handle slash command selection with arrow keys and Enter
                if (this.slashCommandActive && this.slashFilteredReplies.length > 0) {
                    const filteredReplies = this.slashFilteredReplies.filter(r => this.shoud_wb_cannedReplyData(r));

                    if (event.keyCode === 40) { // Down arrow
                        event.preventDefault();
                        // Move to next item or wrap to first
                        this.selectedReplyIndex = (this.selectedReplyIndex + 1) % filteredReplies.length;
                        this.scrollToSelectedItem();
                        return;
                    } else if (event.keyCode === 38) { // Up arrow
                        event.preventDefault();
                        // Move to previous item or wrap to last
                        this.selectedReplyIndex = (this.selectedReplyIndex - 1 + filteredReplies.length) % filteredReplies.length;
                        this.scrollToSelectedItem();
                        return;
                    } else if (event.keyCode === 13 && this.selectedReplyIndex >= 0) { // Enter
                        event.preventDefault();
                        this.selectSlashReply(filteredReplies[this.selectedReplyIndex]);
                        return;
                    } else if (event.keyCode === 27) { // Escape
                        event.preventDefault();
                        this.closeSlashCommand();
                        return;
                    }
                }

                // Original Enter key logic for sending messages
                if (event.keyCode === 13) {
                    if (event.shiftKey) {
                        // Shift+Enter adds new line
                        event.preventDefault();
                        this.wb_newMessage += '\n';
                    } else {
                        // Enter sends message
                        event.preventDefault();
                        if (this.wb_newMessage.trim() !== '') {
                            this.wb_sendMessage();
                        }
                    }
                }
            },

            handleLabelDisplay() {
                // Assuming wb_selectedinteraction is an array with one selected interaction
                let filteredInteractions = [...this.wb_filteredInteractions];
                filteredInteractions.forEach(interaction => {
                    // Find the matching label in wb_requireData.custom_labels
                    const matchingLabel = this.wb_requireData && this.wb_requireData.custom_labels ? this.wb_requireData.custom_labels.find(label => label.id == interaction.label) : undefined;
                    // If a matching label is found, set the name and color
                    let validName = (matchingLabel) ? matchingLabel.label : matchingLabel;
                    let validColor = (matchingLabel) ? matchingLabel.color : matchingLabel;
                    this.labelName.push(validName);
                    this.labelColor.push(validColor);
                });
            },
            // Add this method to reset location data when the modal opens
            openLocationModal() {
                // Reset all location-related values
                this.lat = null;
                this.lng = null;
                this.locationAddress = '';
                this.locationName = '';
                this.isSendingLocation = false;

                // Show the modal
                $('#locationPickerModal').modal('show');

                // Initialize map with default values after modal is fully shown
                $('#locationPickerModal').on('shown.bs.modal', () => {
                    this.$nextTick(() => {
                        this.initMap();
                    });
                });
            },
            getGoogleMapsUrl(message) {
                // Step 1: Fix multiple dashes (e.g., "--70.78" → "-70.78")
                message = message.replace(/--+/g, '-');

                // Step 2: Match numbers with optional minus and decimal
                const matches = message.match(/-?\d+(\.\d+)?/g);

                // Step 3: Validate we got exactly two values
                if (matches && matches.length >= 2) {
                    const lat = parseFloat(matches[0]);
                    const lng = parseFloat(matches[1]);

                    return `https://www.google.com/maps?q=${lat},${lng}`;
                }

                return null;
            },

            initMap() {
                const el = document.getElementById(this.mapId);
                if (!el) return;

                // Set default coordinates if lat/lng are not available
                // Using a default location (you can change these to your preferred default)
                const defaultLat = 28.7041; // Default latitude (London)
                const defaultLng = 77.1025; // Default longitude (London)

                // Use available coordinates or defaults
                const latitude = this.lat !== null && this.lat !== undefined ? this.lat : defaultLat;
                const longitude = this.lng !== null && this.lng !== undefined ? this.lng : defaultLng;

                // Set these values back to the component
                if (this.lat === null || this.lat === undefined) {
                    this.lat = latitude;
                }
                if (this.lng === null || this.lng === undefined) {
                    this.lng = longitude;
                }

                // Check if map is already initialized for this container
                if (this.map) {
                    // If map exists, just update the view and marker position
                    this.map.setView([latitude, longitude], 13);
                    if (this.marker) {
                        this.marker.setLatLng([latitude, longitude]);
                    }
                    return;
                }

                // Create new map only if it doesn't exist
                try {
                    this.map = L.map(el).setView([latitude, longitude], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.marker = L.marker([latitude, longitude], {
                        draggable: true
                    }).addTo(this.map);

                    this.marker.on('dragend', () => {
                        const pos = this.marker.getLatLng();
                        this.lat = pos.lat;
                        this.lng = pos.lng;

                        // If you need to reverse geocode to get address from coordinates
                        // you can add that logic here or call a method to do that
                    });

                    // Invalidate map size after it's loaded to handle any container size issues
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 100);
                } catch (error) {
                    console.error("Error initializing map:", error);
                }
            },

            // If you need to completely destroy and recreate the map
            resetMap() {
                if (this.map) {
                    // Remove the old map completely
                    this.map.remove();
                    this.map = null;
                    this.marker = null;

                    // Now initialize a fresh map
                    this.initMap();
                }
            },
            searchLocation() {
                if (this.locationSearch.trim().length < 3) {
                    this.searchResults = []
                    return
                }

                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.locationSearch)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.searchResults = data
                    })
                    .catch(err => {
                        console.error('Search failed:', err)
                        this.searchResults = []
                    })
            },
            selectSearchResult(result) {
                this.lat = parseFloat(result.lat)
                this.lng = parseFloat(result.lon)
                this.locationAddress = result.display_name
                this.searchResults = []

                if (this.marker && this.map) {
                    this.marker.setLatLng([this.lat, this.lng])
                    this.map.setView([this.lat, this.lng], 13)
                }
            },
            // optional: update map if lat/lng manually typed
            updateMapFromLatLng() {
                if (this.marker && this.map) {
                    this.marker.setLatLng([this.lat, this.lng])
                    this.map.setView([this.lat, this.lng], 13)
                }
            },
            useCurrentLocation() {
                // Set the flag that we're intentionally sending location
                this.isSendingLocation = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        this.lat = latitude.toFixed(6);
                        this.lng = longitude.toFixed(6);

                        // Perform reverse geocoding to get the actual address
                        this.reverseGeocode(latitude, longitude);
                        this.updateMapFromLatLng(); // <--- Important to update the map view too
                    },
                    (error) => {
                        console.error("Geolocation error:", error.message);
                        this.isSendingLocation = false; // Reset flag on error
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            },
            // Add this method to your component
            reverseGeocode(latitude, longitude) {
                // Use Nominatim (OpenStreetMap) for reverse geocoding
                // Note: For production use, consider using a commercial service with proper API key
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`;

                // Show loading state
                this.locationAddress = "Fetching address...";

                fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'User-Agent': 'YourApp/1.0' // Replace with your app name - Nominatim requires a User-Agent
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.display_name) {
                            // Set both location name and address
                            const fullAddress = data.display_name;

                            // Set a shorter name for the location (usually the building/place name or street name)
                            let locationName = "Current Location";

                            // Try to get a more specific location name
                            if (data.address) {
                                // Try to get the most specific location name
                                if (data.address.building) {
                                    locationName = data.address.building;
                                } else if (data.address.amenity) {
                                    locationName = data.address.amenity;
                                } else if (data.address.shop) {
                                    locationName = data.address.shop;
                                } else if (data.address.road) {
                                    const houseNumber = data.address.house_number ? data.address.house_number + " " : "";
                                    locationName = houseNumber + data.address.road;
                                } else if (data.address.suburb) {
                                    locationName = data.address.suburb;
                                } else if (data.address.city || data.address.town || data.address.village) {
                                    locationName = data.address.city || data.address.town || data.address.village;
                                }
                            }

                            // Set the values
                            this.locationAddress = fullAddress;
                            this.locationName = locationName; // You may need to add this property to your data()


                        } else {
                            // Fallback if no address is found
                            this.locationAddress = "Unknown Location";
                            this.locationName = "Current Location";
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching address:", error);
                        // Fallback to default values on error
                        this.locationAddress = "Current Location";
                        this.locationName = "Current Location";
                    });
            },
            mapfixsize() {
                // Reset all location-related values
                this.lat = null;
                this.lng = null;
                this.locationAddress = '';
                this.locationName = '';
                this.isSendingLocation = false;
                if (this.map) {
                    setTimeout(() => {
                        this.map.invalidateSize();
                    }, 500);
                }
            },
            map_init(messages) {
                messages.forEach((msg, index) => {
                    if (msg.type === 'location' && msg.message) {
                        const coords = msg.message.match(/(-?\d+(\.\d+)?)/g);
                        if (coords && coords.length === 2) {
                            const [lat, lng] = coords.map(Number);
                            if (!isNaN(lat) && !isNaN(lng)) {
                                const mapContainerId = `map-location-${index}`;
                                this.$nextTick(() => {
                                    const container = document.getElementById(mapContainerId);
                                    if (container && !container._leaflet_id) {
                                        const map = L.map(mapContainerId).setView([lat, lng], 13);
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: 'OpenStreetMap contributors'
                                        }).addTo(map);

                                        const marker = L.marker([lat, lng]).addTo(map);

                                        const apiKey = "<?= get_option('google_api_key') ?>";
                                        fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.status === 'OK' && data.results.length > 0) {
                                                    const address = data.results[0].formatted_address;
                                                    marker.bindPopup(address).openPopup();
                                                } else {
                                                    marker.bindPopup("Location Shared").openPopup();
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Google Maps reverse geocoding error:', error);
                                                marker.bindPopup("Location Shared").openPopup();
                                            });
                                        // Add the fullscreen control to the map
                                        L.control.fullscreen().addTo(map);
                                    }
                                });
                            }
                        }

                    }
                });
            },
            initLocationMap() {
                this.$nextTick(() => {
                    this.map_init(this.wb_selectedinteraction.messages);
                });
            },

            sanitizeMessage(message) {
                return DOMPurify.sanitize(message, {
                    USE_PROFILES: {
                        html: true
                    }
                });
                return div.textContent || div.innerText || "";
            },
            searchMessages() {
                if (this.searchMessagesText) {

                    const query = this.searchMessagesText.toLowerCase().trim();
                    const hasHtmlChars = /[<>]/.test(query);
                    const isHtmlTag = /^[a-z]+$/.test(query) && document.createElement(query).toString() !==
                        "[object HTMLUnknownElement]";

                    if (hasHtmlChars || isHtmlTag) {
                        this.searchError = "Searching for HTML tags is not allowed.";
                        this.wb_selectedinteraction.messages.forEach(msg => msg.match = false);

                        this.matchedMessages = [];
                        this.updateSearchCounter(0, 0);
                        return;
                    } else {
                        this.searchError = "";
                    }

                    this.matchedMessages = [];

                    this.wb_selectedinteraction.messages.forEach((msg, index) => {
                        const cleanMessage = this.sanitizeMessage(msg.message || '');

                        if (cleanMessage.toLowerCase().includes(query)) {
                            msg.match = true;
                            this.matchedMessages.push({
                                messageIndex: index,
                                position: cleanMessage.indexOf(query)
                            });
                        } else {
                            msg.match = false;
                        }
                    });

                    this.$nextTick(() => {
                        setTimeout(() => {
                            const highlights = document.querySelectorAll('.highlight');

                            this.matchedMessages = Array.from(highlights);

                            this.matchIndex = 0;

                            if (this.matchedMessages.length > 0) {
                                this.scrollToMatch();
                            }

                            this.updateSearchCounter(
                                this.matchedMessages.length > 0 ? 1 : 0,
                                this.matchedMessages.length
                            );
                        }, 100);
                    });
                } else {
                    this.wb_selectedinteraction.messages.forEach(msg => msg.match = false);
                    this.matchedMessages = [];
                    this.updateSearchCounter(0, 0);
                }
            },
            updateSearchCounter(current, total) {

                let counter = document.getElementById('search-counter');
                if (!counter) {
                    counter = document.createElement('span');
                    counter.id = 'search-counter';
                    counter.className = 'text-sm text-gray-600 dark:text-gray-400 ml-2';
                    const searchContainer = document.querySelector('.search-container');
                    if (searchContainer) {
                        searchContainer.appendChild(counter);
                    }
                }

                // Prevent unnecessary updates
                if (counter.textContent !== `${current} of ${total}`) {
                    counter.textContent = total > 0 ? `${current} of ${total}` : (this.searchMessagesText ?
                        'No matches' : '');
                }
            },
            scrollToMatch() {
                if (this.matchedMessages.length === 0) return;
                // Remove highlighting from all matches
                this.matchedMessages.forEach(el => {
                    el.classList.remove('active-highlight');
                });

                // Get the current highlight element
                const currentHighlight = this.matchedMessages[this.matchIndex];

                if (currentHighlight) {
                    // Add active class to current highlight
                    currentHighlight.classList.add('active-highlight');

                    // Scroll the highlight into view
                    currentHighlight.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });

                    // Update the counter
                    this.updateSearchCounter(this.matchIndex + 1, this.matchedMessages.length);
                }
            },

            nextMatch() {
                if (this.matchedMessages.length === 0) return;

                this.matchIndex = (this.matchIndex + 1) % this.matchedMessages.length;
                this.scrollToMatch();
            },

            prevMatch() {
                if (this.matchedMessages.length === 0) return;

                this.matchIndex = (this.matchIndex - 1 + this.matchedMessages.length) % this.matchedMessages.length;
                this.scrollToMatch();
            },

            highlightSearch(text) {

                if (this.searchError !== '' || !this.searchMessagesText || !text) return text;

                const sanitizedText = this.sanitizeMessage(text);

                const query = this.searchMessagesText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');

                // Replace with highlight spans
                return sanitizedText.replace(
                    new RegExp(`(${query})`, "gi"),
                    `<span class="tw-bg-warning-300/100 dark:tw-bg-black px-1 py-0.5 rounded highlight">$1</span>`
                );
            },
            toggleMessageSearch() {
                this.wb_messagesSearch = !this.wb_messagesSearch;
                if (this.wb_messagesSearch) {
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                    });
                }
            },
            handlerequiredata() {
                $.ajax({
                    url: '<?= admin_url('whatsbot/get_chat_required_data'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    success: (response) => {
                        this.wb_requireData = response;
                    },
                })
            },
            handleBadgeDisplay() {
                let filteredInteractions = [this.wb_selectedinteraction];
                this.badgeGroupNames = [];
                filteredInteractions.forEach(interaction => {
                    // Check if the interaction type is 'contacts'
                    if (interaction.type === "contacts") {
                        if (interaction.group) {
                            // Iterate through each group in interaction.groups
                            const groupIds = interaction.group.map(item => item.groupid);
                            groupIds.forEach(groupId => {
                                const matchingContactGroup = this.wb_requireData && this.wb_requireData.contact_groups ? this.wb_requireData.contact_groups.find(contactGroup => contactGroup.id === groupId) : undefined;
                                if (matchingContactGroup) {
                                    this.badgeGroupNames.push(matchingContactGroup.name);
                                }
                            });

                        }
                    } else if (interaction.type === "leads") {
                        // Your existing logic for leads

                        let matchingSource = this.wb_requireData && this.wb_requireData.lead_sources ?
                            this.wb_requireData.lead_sources.find(source => source.id === interaction.source) :
                            undefined;
                        if (matchingSource) {
                            this.badgeSourceName = matchingSource.name;
                        }

                        let matchingStatus = this.wb_requireData && this.wb_requireData.lead_status ?
                            this.wb_requireData.lead_status.find(status => status.id === interaction.status) :
                            undefined;
                        if (matchingStatus) {
                            this.badgeStatusName = matchingStatus.name;
                            this.badgeStatusColor = matchingStatus.color;
                        }
                    }
                });
            },

            toggleDarkMode() {
                this.darkMode = !this.darkMode;
                document.documentElement.classList.toggle('dark', this.darkMode);
                localStorage.setItem('darkMode', this.darkMode.toString());
            },
            formatMessage(text) {
                // First, highlight the search term if any
                text = this.highlightSearch(text);

                // Then replace newlines with <br> for display formatting
                return text.replace(/\n/g, '<br>');
            },
            parseContactCard(message) {
                const fallback = {
                    name: '',
                    phone: ''
                };
                const text = String(message || '').trim();
                if (!text) {
                    return fallback;
                }

                const normalizeContact = (parsed) => {
                    if (!parsed || typeof parsed !== 'object') {
                        return fallback;
                    }

                    const name = parsed.name || parsed.contact_name || parsed.formatted_name || parsed.full_name || parsed.display_name || '';
                    const phone = parsed.phone || parsed.contact_phone || parsed.wa_id || parsed.number || parsed.mobile || '';

                    return {
                        name: String(name || '').trim(),
                        phone: String(phone || '').trim(),
                    };
                };

                try {
                    const parsed = JSON.parse(text);
                    const contact = normalizeContact(parsed);
                    if (contact.name || contact.phone) {
                        return contact;
                    }
                } catch (e) {
                    // Ignore parse failures and try legacy text formats below.
                }

                const match = text.match(/Contact:\s*(.*?)\s*\((.*?)\)\s*$/i);
                if (match) {
                    return {
                        name: match[1] ? match[1].trim() : '',
                        phone: match[2] ? match[2].trim() : '',
                    };
                }

                return fallback;
            },
            getContactDisplay(message) {
                const contact = this.parseContactCard(message);
                const name = contact.name || '';
                const phone = contact.phone || '';
                const label = name || phone ? `${name}` : '';

                return {
                    ...contact,
                    label,
                };
            },
            formatInteractionPreview(interaction) {
                if (!interaction) {
                    return '';
                }
                
                if (interaction.last_message.startsWith('[') && interaction.last_message.includes('"name"')) {
                    try {
                        const items = JSON.parse(interaction.last_message);

                        return 'Order received.';

                    } catch (e) {
                        // fallback
                    }
                }

                const contact = this.parseContactCard(interaction.last_message);
                if (contact.name || contact.phone || interaction.type === 'contact' || interaction.type === 'contacts') {
                    const name = contact.name || '';
                    const phone = contact.phone || '';
                    if (name || phone) {
                        return this.highlightSearch(`${name}${phone ? ` (${phone})` : ''}`);
                    }
                }

                return this.wb_truncateText(interaction.last_message || '', 20);
            },
            copyContactNumber(phone) {
                const number = String(phone || '').trim();
                if (!number) {
                    alert_float('danger', 'No contact number available to copy.');
                    return;
                }

                const copyFallback = () => {
                    const tempInput = document.createElement('input');
                    tempInput.value = number;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    alert_float('success', 'Contact number copied to clipboard.');
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(number)
                        .then(() => alert_float('success', 'Contact number copied to clipboard.'))
                        .catch(() => copyFallback());
                } else {
                    copyFallback();
                }
            },

            wb_typeModal_Open() {
                if (this.wb_selectedinteraction.type === 'contacts') {
                    contact(this.wb_selectedinteraction.client_id, this.wb_selectedinteraction.type_id);
                } else if (this.wb_selectedinteraction.type === 'leads') {
                    init_lead(this.wb_selectedinteraction.type_id);
                }
            },
            wb_noteModal_Open() {
                let url;
                if (this.wb_selectedinteraction.type === 'contacts') {
                    url = `<?= admin_url('clients/client/') ?>${this.wb_selectedinteraction.client_id}?group=notes`;
                } else if (this.wb_selectedinteraction.type === 'leads') {
                    this.init_lead_notes(this.wb_selectedinteraction.type_id);
                } else if (this.wb_selectedinteraction.type === 'customer') {
                    url = `<?= admin_url('clients/client/') ?>${this.wb_selectedinteraction.type_id}?group=notes`;
                }
                if (url) {
                    window.open(url, '_blank');
                }
            },
            init_lead_notes(type_id) {
                init_lead(this.wb_selectedinteraction.type_id);
                // After opening the modal, activate the `#lead_notes` tab
                setTimeout(() => {
                    // Remove `active` class from all <li role="presentation"> elements
                    document.querySelectorAll('li[role="presentation"]').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Add `active` class to the Notes tab's <li>
                    const notesTab = document.querySelector('li[role="presentation"] a[href="#lead_notes"]');
                    if (notesTab && notesTab.parentElement) {
                        notesTab.parentElement.classList.add('active');
                    }

                    // Remove `active` from all tab contents
                    document.querySelectorAll('.tab-pane').forEach(content => {
                        content.classList.remove('active');
                    });

                    // Add `active` to the #lead_notes tab content
                    const leadNotesContent = document.getElementById('lead_notes');
                    if (leadNotesContent) {
                        leadNotesContent.classList.add('active');
                    }
                }, 2000);
            },
            wb_fetchCustomPrompts() {
                $.ajax({
                    url: `${admin_url}whatsbot/ai_prompts/get`,
                    type: 'POST',
                    dataType: 'json',
                    success: (response) => {
                        if (Array.isArray(response.custom_prompts)) {
                            this.customPrompts = response.custom_prompts.map(prompt => ({
                                label: prompt.name,
                                action: prompt.action,
                                is_public: prompt.is_public,
                                added_from: prompt.added_from

                            }));
                        } else {
                            console.error('Invalid response structure', response);
                        }
                    },
                    error: (error) => {
                        console.error('Error fetching canned replies:', error);
                    }
                });
            },
            shouldDisplayPrompt(prompt) {
                return this.wb_login_staff_id === prompt.added_from || this.has_pemission_view_ai_prompts;
            },

            toggleCannedReplies() {
                this.cannedRepliesVisible = !this.cannedRepliesVisible;
            },
            wb_TemplateModal() {
                const modalDialog = $('#template-modal .modal-dialog');
                const columnDivs = $('.col-md-12, .col-md-4');

                modalDialog.removeClass('modal-xxl').addClass('modal-md');
                columnDivs.removeClass('col-md-4').addClass('col-md-12');

                const form = document.getElementById('chat_form');
                form.reset();
                this.$nextTick(() => {
                    $('#rel_type').val(this.wb_selectedInteraction['type']);
                    $('#rel_type').trigger('change');
                });
                $('.variableDetails').addClass('hide');
                $('.selectpicker').selectpicker('refresh');
                $('#template-modal').modal('show');
                // Change modal size and columns when a value is selected
                $('#template_id').on('change', function() {
                    const modalDialog = $('#template-modal .modal-dialog');
                    const columnDivs = $('.col-md-12, .col-md-4');

                    // Change modal size to modal-xxl
                    modalDialog.removeClass('modal-md').addClass('modal-xxl');

                    // Change columns to col-md-4
                    columnDivs.removeClass('col-md-12').addClass('col-md-4');
                });
            },
            wb_cannedReplyData() {
                $.ajax({
                    url: `${admin_url}whatsbot/canned_reply/get`,
                    type: 'POST',
                    dataType: 'html',
                    success: (response) => {
                        const parsedResponse = JSON.parse(response);
                        if (parsedResponse.reply_data && Array.isArray(parsedResponse.reply_data)) {
                            this.cannedReplies = parsedResponse.reply_data.map(reply => ({
                                title: reply.title,
                                description: reply.description,
                                is_public: reply.is_public,
                                added_from: reply.added_from
                            }));
                            this.cannedRepliesVisible = false;
                        } else {
                            console.error('Invalid response structure');
                        }
                    },
                    error: (error) => {
                        console.error('Error fetching canned replies:', error);
                    }
                });
            },
            shoud_wb_cannedReplyData(reply) {
                return reply.is_public === '1' || this.wb_login_staff_id === reply.added_from || this.has_pemission_view_canned_reply;
            },
            addToMessage(reply) {
                this.wb_newMessage = `${reply.description}`;
                this.cannedRepliesVisible = false;
                this.$refs.inputField.focus();
            },

            wb_selectinteraction(id) {
                $.ajax({
                    url: `${admin_url}whatsbot/chat_mark_as_read`,
                    type: 'POST',
                    dataType: 'html',
                    data: {
                        'interaction_id': id
                    },
                })
                // find index in the full interactions array (for messages etc.)
                const globalIndex = this.interactions.findIndex(interaction => interaction.id === id);
                // find index in the currently displayed list (for labelName/labelColor arrays)
                const displayedIndex = this.wb_displayedInteractions.findIndex(interaction => interaction.id === id);

                if (globalIndex !== -1) {
                    this.wb_selectedinteractionIndex = globalIndex;
                    this.wb_selectedinteractionDisplayedIndex = displayedIndex; // may be -1 if not present
                    this.wb_selectedinteraction = this.interactions[globalIndex];

                    this.wb_selectedinteraction.messages.forEach(message => {
                        if (message.is_read == 0) {
                            message.is_read = 1;
                        }
                    });
                    this.wb_selectedinteractionId = this.wb_selectedinteraction['id'];
                    this.wb_selectedinteractionMobNo = this.wb_selectedinteraction['receiver_id'];
                    this.wb_selectedinteractionSenderNo = this.wb_selectedinteraction['wa_no'];
                    this.wb_scrollToBottom();
                    this.handleBadgeDisplay();
                    this.wb_fetchCustomPrompts();
                    this.wb_cannedReplyData();
                    this.initLocationMap();
                    this.isShowChatMenu = false;
                    this.isShowUserChat = true;
                    this.$nextTick(() => {
                        $('#rel_type').val(this.wb_selectedInteraction['type']);
                        $('#rel_type').trigger('change');
                    });
                }
            },


            trimMessage(message, maxLength = 100) {
                const sanitizedMessage = this.sanitizeMessage(message);
                if (sanitizedMessage.length > maxLength) {
                    return sanitizedMessage.substring(0, maxLength) + '...';
                }
                return sanitizedMessage;
            },

            getOriginalMessage(refMessageId) {
                const message = this.wb_selectedinteraction.messages.find(msg => msg.message_id === refMessageId) || {};
                return {
                    ...message,
                    message: this.trimMessage(message.message),
                    assets_url: message.asset_url || ''
                };
            },
            getMessageReactions(messageId) {
                if (!this.wb_selectedinteraction || !Array.isArray(this.wb_selectedinteraction.messages) || !messageId) {
                    return [];
                }

                return this.wb_selectedinteraction.messages.filter(msg => {
                    return msg.type === 'reaction' && msg.ref_message_id === messageId;
                });
            },
            replyToMessage(message) {
                this.replyingToMessage = message || message.asset_url;
                this.wb_scrollToBottom();
            },
            clearReply() {
                this.replyingToMessage = null;
            },
            wb_initAgent() {
                const agentId = this.wb_selectedinteraction.agent.agent_id;
                this.selectedStaffId = agentId;
                $('#AgentModal').modal('show');
                setTimeout(function() {
                    $('#AgentModal').find('select[name="assigned[]"]').val(agentId);
                    $('#AgentModal').find('select[name="assigned[]"]').trigger('change');
                }, 100);
            },
            wb_initLabel() {
                $('#LabelModal').modal('show');
                $('#label').val(this.wb_selectedinteraction.label);
                $('.selectpicker').selectpicker('refresh');
            },
            wb_handleAssignedChange(event) {
                const id = this.wb_selectedinteraction ? this.wb_selectedinteraction.id : null; // Get the current interaction ID
                const staffId = $('select[name="assigned[]"]').val(); // Get the selected staff ID from the dropdown
                // Send the selected staff ID to the server via AJAX
                $.ajax({
                        url: `${admin_url}whatsbot/assign_staff`,
                        type: 'POST',
                        dataType: 'html',
                        data: {
                            'staff_id': staffId, // Send selected staff ID
                            'interaction_id': id // Send interaction ID
                        },
                    })
                    .done((res) => {
                        // After the request is successful, update the agent_id in wb_selectedinteraction
                        if (this.wb_selectedinteraction) {
                            res = JSON.parse(res)

                            // Replace the agent_id in the current interaction's agent object
                            this.wb_selectedinteraction.agent_icon = res.agent_icon;
                            this.wb_selectedinteraction.agent_name = res.agent_name;

                        }
                        // Re-select the interaction to refresh the view
                        this.wb_selectinteraction(id); // Call the method to refresh the selected interaction
                    });
            },
            wb_handleLabelChange() {
                const label = $('select[name="label"]').val();
                const id = this.wb_selectedinteraction ? this.wb_selectedinteraction.id : null;
                $.ajax({
                    url: `${admin_url}whatsbot/custom_label/assign_label`,
                    type: 'POST',
                    dataType: 'html',
                    data: {
                        'label': label, // Send selected staff ID
                        'interaction_id': id // Send interaction ID
                    },
                    success: (response) => {
                        if (this.wb_selectedinteraction) {
                            this.wb_selectedinteraction.label = label; // Set the selected label ID
                        }
                        const matchingLabel = this.wb_requireData.custom_labels.find(lbl => lbl.id == this.wb_selectedinteraction.label);
                        // Find the index of the current interaction to replace the existing value
                        const interactionIndex = this.wb_filteredInteractions.findIndex(interaction => interaction.id === id);
                        if (interactionIndex !== -1) {
                            // Replace the label name and color at the found index
                            this.labelName[interactionIndex] = matchingLabel ? matchingLabel.label : "";
                            this.labelColor[interactionIndex] = matchingLabel ? matchingLabel.color : "";
                        }
                    },
                })
            },
            wb_deleteInteraction(id) {
                if (confirm_delete()) {
                    $.ajax({
                        url: `${admin_url}whatsbot/delete_chat`,
                        type: 'POST',
                        dataType: 'html',
                        data: {
                            'interaction_id': id
                        },
                    }).done((res) => { // Use an arrow function here
                        if (res) {
                            alert_float('danger', "<?= _l('deleted', _l('chat')); ?>");
                            // Remove the interaction from both arrays
                            this.interactions = this.interactions.filter(interaction => interaction.id !== id);
                            this.wb_displayedInteractions = this.interactions;
                            if (this.wb_selectedinteractionId === id) {
                                this.wb_selectedinteraction = null;
                                this.wb_selectedinteractionId = null;
                                this.wb_selectedinteractionIndex = -1;
                                this.wb_selectedinteractionMobNo = null;
                                this.wb_selectedinteractionSenderNo = null;
                                this.isShowUserChat = false; // Hide the chat UI when interaction is deleted
                            }
                        }
                    });
                }
            },
            async wb_sendMessage() {

                if (this.wb_default_number != this.wb_selectedinteraction.wa_no) {
                    this.sameNumErrorMessage = "<?= _l('you_cannot_send_a_message_using_this_number'); ?>";
                    return;
                }
                if (!this.wb_selectedinteraction) return;
                const wb_formData = new FormData();
                wb_formData.append('id', this.wb_selectedinteraction.id);
                wb_formData.append('to', this.wb_selectedinteraction.receiver_id);
                wb_formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
                wb_formData.append('type', this.wb_selectedinteraction.type);
                wb_formData.append('type_id', this.wb_selectedinteraction.type_id);
                const MAX_MESSAGE_LENGTH = 2000;
                if (this.wb_newMessage.length > MAX_MESSAGE_LENGTH) {
                    this.wb_newMessage = this.wb_newMessage.substring(0, MAX_MESSAGE_LENGTH);
                }

                // We need to track whether we want to send location
                // Create a flag to explicitly track if this is a location message
                // This should be set by your UI when the user chooses to send a location
                if (!this.hasOwnProperty('isSendingLocation')) {
                    this.isSendingLocation = false;
                }

                // If we have lat/lng and are explicitly sending a location OR the send location flag is set
                if ((this.lat && this.lng) && (this.isSendingLocation || this.locationAddress)) {

                    const latitude = `${this.lat}`;
                    const longitude = `${this.lng}`;
                    const locationName = this.locationAddress ? `${this.locationAddress}` : "My Location";
                    const locationAddress = this.locationAddress ? `${this.locationAddress}` : "Current Location";
                    wb_formData.append('latitude', latitude);
                    wb_formData.append('longitude', longitude);
                    wb_formData.append('locationName', locationName);
                    wb_formData.append('locationAddress', locationAddress);
                }

                // Add message if it exists
                if (this.wb_newMessage.trim()) {
                    wb_formData.append('message', DOMPurify.sanitize(this.wb_newMessage));
                }

                // Handle image attachment
                if (this.wb_imageAttachment) {
                    wb_formData.append('image', this.wb_imageAttachment);
                }

                // Handle video attachment
                if (this.wb_videoAttachment) {
                    wb_formData.append('video', this.wb_videoAttachment);
                }

                // Handle document attachment
                if (this.wb_documentAttachment) {
                    wb_formData.append('document', this.wb_documentAttachment);
                }

                // Handle audio attachment
                if (this.wb_audioBlob) {
                    wb_formData.append('audio', this.wb_audioBlob, 'audio.mp3');
                }

                if (this.replyingToMessage) {
                    wb_formData.append('ref_message_id', this.replyingToMessage.message_id);
                }

                try {
                    const wb_response = await axios.post('<?php echo admin_url('whatsbot/whatsapp_webhook/send_message'); ?>', wb_formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    // Clear all input data including location data
                    this.wb_newMessage = '';
                    this.wb_imageAttachment = null;
                    this.wb_videoAttachment = null;
                    this.wb_documentAttachment = null;
                    this.wb_audioBlob = null;
                    this.wb_imagePreview = '';
                    this.wb_videoPreview = '';

                    // Clear location data
                    if (this.isSendingLocation || (this.lat && this.lng)) {
                        this.lat = null;
                        this.lng = null;
                        this.locationAddress = '';
                        this.isSendingLocation = false;
                        $('#locationPickerModal').modal('hide');
                        this.showAttachmentOptions = false;
                    }

                    this.wb_filterInteractions();
                    this.wb_selectinteraction(this.wb_selectedinteraction.id);
                    this.errorMessage = '';
                    this.clearReply();
                    this.wb_scrollToBottom();
                    this.wb_selectedinteractionIndex = 0;
                } catch (error) {
                    const wb_rawErrorMessage = error.response && error.response.data ? error.response.data : 'An error occurred. Please try again.';
                    // Define regular expressions to match the desired parts of the HTML error message
                    const wb_typeRegex = /<p>Type: (.+)<\/p>/;
                    const wb_messageRegex = /<p>Message: (.+)<\/p>/;
                    // Extract the type and message from the HTML error message
                    const wb_typeMatch = wb_rawErrorMessage.match(wb_typeRegex);
                    var wb_messageMatch = wb_rawErrorMessage.match(wb_messageRegex);
                    if (wb_messageMatch != null) {
                        if (typeof(wb_messageMatch[1] == 'object')) {
                            wb_messageMatch[1] = JSON.parse(wb_messageMatch[1]);
                            wb_messageMatch[1] = wb_messageMatch[1].error.message;
                        }
                    }
                    const wb_getTypeText = wb_typeMatch ? wb_typeMatch[1] : '';
                    const wb_getMessageText = wb_messageMatch ? wb_messageMatch[1] : '';
                    // Construct the error message by concatenating the extracted text content
                    const errorMessage = wb_getTypeText.trim() + '\n' + wb_getMessageText.trim();
                    this.errorMessage = errorMessage;
                }
            },
            initializePusher() {
                if (!this.wb_pusher_api_key || !this.wb_pusher_cluster) {
                    return; // Exit the method if either is null
                }
                // Initialize Pusher with your app key and cluster
                const pusher = new Pusher(this.wb_pusher_api_key, {
                    cluster: this.wb_pusher_cluster,
                    encrypted: true,
                });
                // Subscribe to the 'interactions-channel'
                const channel = pusher.subscribe('interactions-channel');
                // Listen for the 'interaction-update' event
                channel.bind('new-message-event', (data) => {
                    // Update interactions based on real-time data from Pusher
                    this.appendNewInteractions(data.interaction);
                });
            },
            appendNewInteractions(newInteractions) {
                const staff_id = this.wb_login_staff_id;
                const enable_supportagent = "<?= get_option('enable_supportagent'); ?>";
                const isAdmin = <?php echo is_admin() ? 'true' : 'false'; ?>;
                const existingInteractions = [...this.interactions]; // Existing interactions array
                const index = existingInteractions.findIndex(interaction => interaction.id === newInteractions.id); //matching interaction id to newInteractions id
                if (index !== -1) { //interaction IDs match, replace the whole existing message with the new message
                    const existingInteraction = existingInteractions[index];
                    // Create a new object that contains all properties from newInteractions except messages
                    const updatedInteraction = {
                        ...existingInteraction, // Existing properties
                        ...newInteractions, // Spread newInteractions properties
                        messages: existingInteraction.messages // Keep the original messages for now
                    };
                    const find_msg_index = existingInteractions?.[index]?.messages?.findIndex(
                        interaction => interaction?.id === newInteractions?.messages?.id
                    ) ?? -1; //matching interaction messages id to newInteractions messages id
                    if (find_msg_index !== -1) {
                        const messageIndex = newInteractions?.messages?.id; // Access the ID of newInteraction.messages
                        if (messageIndex === existingInteractions[index]?.messages[find_msg_index]?.id) {
                            // If IDs match, replace the whole existing message with the new message

                            existingInteractions[index].messages[find_msg_index] = {
                                ...newInteractions.messages
                            };
                            // Re-render map only for updated messages
                            this.$nextTick(() => {
                                if (existingInteractions?.[index]?.messages) {
                                    this.map_init(existingInteractions[index].messages);
                                }
                            });

                        }
                    } else {
                        existingInteractions[index].messages.push(newInteractions.messages);
                        this.$nextTick(() => {
                            if (existingInteractions?.[index]?.messages) {
                                this.map_init(existingInteractions[index].messages);
                            }
                        });
                    }
                    existingInteractions[index] = updatedInteraction;
                    this.$nextTick(() => {
                        if (existingInteractions?.[index]?.messages) {
                            this.map_init(existingInteractions[index].messages);
                        }
                    });
                } else {
                    // Ensure newInteractions.messages is an array or initialize it as an empty array
                    if (!Array.isArray(newInteractions.messages)) {
                        newInteractions.messages = [newInteractions.messages];
                    }
                    // If the interaction id does not exist, push newInteractions directly
                    existingInteractions.push({
                        ...newInteractions,
                        messages: [...newInteractions.messages] // Ensure messages is properly handled
                    });
                    this.$nextTick(() => {
                        if (existingInteractions?.[index]?.messages) {
                            this.map_init(existingInteractions[index].messages);
                        }
                    });

                }

                // Now sort the `existingInteractions` array by `time_sent`
                existingInteractions.sort((a, b) => {
                    const timeA = new Date(a.time_sent);
                    const timeB = new Date(b.time_sent);
                    return timeB - timeA; // Sort descending (latest first)
                });

                // Set the sorted array to `this.wb_displayedInteractions`
                this.wb_displayedInteractions = existingInteractions;
                if (!isAdmin && enable_supportagent == 1) {
                    const filteredNewInteractions = existingInteractions.filter(interaction => {
                        const chatagent = interaction.agent;
                        if (chatagent) {
                            const agentIds = Array.isArray(chatagent.agent_id) ? chatagent.agent_id : [chatagent.agent_id];
                            const assignIds = Array.isArray(chatagent.assign_id) ? chatagent.assign_id : [chatagent.assign_id];
                            // Check if `staff_id` is included in either `agentIds` or `assignIds`
                            return agentIds.includes(staff_id) || assignIds.includes(staff_id);
                        }
                        return [];
                    });
                    this.interactions = this.interactions.filter(
                        existing => !filteredNewInteractions.some(newInteraction => newInteraction.id === existing.id)
                    );
                    // Append new interactions to the existing ones
                    this.interactions = [...this.interactions, ...filteredNewInteractions];
                } else {
                    // Append new interactions for admins
                    this.interactions = existingInteractions;
                }
                // Call your existing methods after updating interactions
                this.wb_filterInteractions();
                this.wb_updateSelectedInteraction();
            },
            wb_updateSelectedInteraction() {
                const wb_new_index = this.interactions.findIndex(interaction => interaction.receiver_id === this.wb_selectedinteractionMobNo && interaction.wa_no === this.wb_selectedinteractionSenderNo && interaction.id === this.wb_selectedinteractionId);
                this.wb_selectedinteraction = this.interactions[wb_new_index];
            },
            wb_getTime(timeString) {
                const date = new Date(timeString);
                const hour = date.getHours();
                const minute = date.getMinutes();
                const period = hour < 12 ? 'AM' : 'PM';
                const formattedHour = hour % 12 || 12;
                return `${formattedHour}:${minute < 10 ? '0' + minute : minute} ${period}`;
            },
            getDate(dateString) {
                const wb_date = new Date(dateString);
                const wb_options = {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                };
                return wb_date.toLocaleDateString('en-GB', wb_options).replace(' ', '-').replace(' ', '-');
            },
            wb_shouldShowDate(currentMessage, previousMessage) {
                if (!previousMessage) return true;
                return this.getDate(currentMessage.time_sent) !== this.getDate(previousMessage.time_sent);
            },
            wb_scrollToBottom() {
                this.$nextTick(() => {
                    const wb_chatContainer = this.$refs.wb_chatContainer;
                    if (wb_chatContainer) {
                        wb_chatContainer.scrollTop = wb_chatContainer.scrollHeight;
                    }
                });
            },
            wb_getAvatarInitials(name) {
                const wb_words = name.split(' ');
                const wb_initials = wb_words.slice(0, 2).map(word => word.charAt(0)).join('');
                return wb_initials.toUpperCase();
            },
            playNotificationSound() {
                var enableSound = "<?= get_option('enable_wtc_notification_sound'); ?>";

                if (enableSound == 1) {
                    var audio = new Audio('<?= module_dir_url('whatsbot', 'assets/audio/whatsapp_notification.mp3'); ?>');
                    audio.play();
                }
            },
            wb_countUnreadMessages(interactionId) {
                const interaction = this.interactions ? this.interactions.find(inter => inter.id === interactionId) : undefined;
                if (interaction) {
                    return interaction.messages.filter(message => message.is_read == 0).length;
                }
                return 0;
            },
            async wb_toggleRecording() {
                if (!this.wb_recording) {

                    this.wb_startRecording();
                } else {

                    this.wb_stopRecording();
                }
            },
            wb_startRecording() {
                // Initialize Recorder.js if not already initialized
                if (!this.recorder) {
                    this.recorder = new Recorder({
                        type: "mp3",
                        sampleRate: 16000,
                        bitRate: 16,
                        onProcess: (buffers, powerLevel, bufferDuration, bufferSampleRate) => {

                        }
                    });
                }
                this.recorder.open((stream) => {
                    this.wb_recording = true;
                    this.recorder.start();
                }, (err) => {
                    console.error("Failed to start wb_recording:", err);

                });
            },
            wb_stopRecording() {
                if (this.recorder && this.wb_recording) {
                    this.recorder.stop((blob, duration) => {
                        this.recorder.close();
                        this.wb_recording = false;
                        this.wb_audioBlob = blob;
                        this.wb_sendMessage();
                        this.wb_recordedAudio = URL.createObjectURL(blob);
                    }, (err) => {
                        console.error("Failed to stop wb_recording:", err);

                    });
                }
            },
            wb_handleImageAttachmentChange(event) {
                this.wb_imageAttachment = event.target.files[0];
                this.wb_imagePreview = URL.createObjectURL(this.wb_imageAttachment);
                this.showAttachmentOptions = false;
            },
            wb_handleVideoAttachmentChange(event) {
                this.wb_videoAttachment = event.target.files[0];
                this.wb_videoPreview = URL.createObjectURL(this.wb_videoAttachment);
                this.showAttachmentOptions = false;
            },
            wb_handleDocumentAttachmentChange(event) {
                this.wb_documentAttachment = event.target.files[0];
                this.showAttachmentOptions = false;
            },
            wb_removeImageAttachment() {
                this.wb_imageAttachment = null;
                this.wb_imagePreview = '';
            },
            wb_removeVideoAttachment() {
                this.wb_videoAttachment = null;
                this.wb_videoPreview = '';
            },
            wb_removeDocumentAttachment() {
                this.wb_documentAttachment = null;
            },
            wb_formatTime(timestamp) {
                const currentDate = new Date();
                const messageDate = new Date(timestamp);
                const diffInMs = currentDate - messageDate;
                const diffInHours = diffInMs / (1000 * 60 * 60);

                if (diffInHours < 24) {
                    // Less than 24 hours, display time
                    const hour = messageDate.getHours();
                    const minute = messageDate.getMinutes();
                    const period = hour < 12 ? 'AM' : 'PM';
                    const formattedHour = hour % 12 || 12;
                    return `${formattedHour}:${minute < 10 ? '0' + minute : minute} ${period}`;
                } else {
                    // More than 24 hours, display wb_date in dd-mm-yy format
                    const day = messageDate.getDate();
                    const month = messageDate.getMonth() + 1;
                    const year = messageDate.getFullYear() % 100; // Get last two digits of the year
                    return `${day}-${month < 10 ? '0' + month : month}-${year}`;
                }
            },
            wb_isServiceWindowOpen(lastMsgTime) {
                if (!lastMsgTime) return false;
                const messageDate = new Date(lastMsgTime);
                const now = new Date();
                const diffInMs = now - messageDate;
                const diffInHours = diffInMs / (1000 * 60 * 60);
                return diffInHours < 24;
            },
            async wb_sendInternalNote() {
                if (!this.wb_selectedinteraction || !this.wb_internalNote.trim()) return;
                try {
                    const formData = new FormData();
                    formData.append('interaction_id', this.wb_selectedinteraction.id);
                    formData.append('note', this.wb_internalNote);
                    formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
                    const res = await axios.post('<?php echo admin_url("whatsbot/whatsapp_webhook/send_internal_note"); ?>', formData);
                    if (res.data.status) {
                        if (!this.wb_selectedinteraction.messages) {
                            this.wb_selectedinteraction.messages = [];
                        }

                        this.wb_selectedinteraction.messages.push({
                            message: this.wb_internalNote,
                            type: 'internal_note',
                            sender_id: 'internal',
                            staff_id: '<?= get_staff_user_id(); ?>',
                            time_sent: new Date().toISOString().slice(0, 19).replace('T', ' '),
                            is_system: 1
                        });
                        this.wb_internalNote = '';
                        alert_float('success', '<?= _l("internal_note_saved"); ?>');
                    }
                } catch (e) {
                    alert_float('danger', e.message);
                }
            },
            wb_alertTime(lastMsgTime) {
                const timezone = "<?= get_option('default_timezone'); ?>"; // Set the desired timezone
                if (lastMsgTime) {
                    // Parse the last message time in the given timezone
                    const messageDate = new Date(lastMsgTime);
                    // Get the current date and time in the specified timezone
                    const currentDate = new Date(new Date().toLocaleString("en-US", {
                        timeZone: timezone
                    }));
                    const diffInMs = currentDate - messageDate;
                    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60)); // Round down to nearest hour
                    const diffInMinutes = Math.floor((diffInMs % (1000 * 60 * 60)) / (1000 * 60)); // Calculate remaining minutes
                    // Check if the difference is less than 24 hours
                    if (diffInHours < 24) {
                        // Calculate remaining time within 24 hours
                        const remainingHours = 23 - diffInHours; // Subtract one hour from 24
                        const remainingMinutes = 60 - diffInMinutes;
                        return `Reply within ${remainingHours} hours and ${remainingMinutes} minutes`;
                    } else {
                        return null;
                    }
                } else {
                    return lastMsgTime;
                }
            },
            wb_stripHTMLTags(str) {
                return str ? str.replace(/<\/?[^>]+(>|$)/g, "") : "";
            },
            wb_truncateText(text, charLimit) {
                const strippedText = this.wb_stripHTMLTags(text);
                if (strippedText.length > charLimit) {
                    return strippedText.slice(0, charLimit) + '...';
                }
                return strippedText;
            },
            wb_filterInteractions() {
                let filtered = this.interactions;

                if (this.wb_selectedWaNo !== "*") {
                    filtered = filtered.filter(interaction => interaction.wa_no === this.wb_selectedWaNo);
                }

                this.wb_filteredInteractions = filtered;
                this.wb_displayedInteractions = this.wb_filteredInteractions;
            },
            wb_searchInteractions() {
                const searchText = this.wb_searchText.trim();
                if (searchText) {
                    // Server-side search: fetch from backend with search param, reset pagination
                    this.getChats(0, searchText, true);
                } else {
                    // If search is empty, just show filtered interactions (no request)
                    this.getChats(0, '', true);
                }
            },
            resetFilters() {
                this.selectedTab = 'searching',
                    this.noResultsMessage = '';
                this.wb_filterInteractions();
            },
            wb_handleAllFilters(e) {
                const selectedRelationType = this.wb_reltype_filter;
                const selectedLabels = this.wb_labels_filter;
                const selectedSource = this.wb_lead_sources; // For leads
                const selectedStatus = this.wb_lead_status; // For leads
                const selectedGroup = this.wb_contact_groups; // For contacts
                const selectedAgent = this.wb_agents_filter; // For agents
                // Start with all filtered interactions
                let filteredInteractions = [...this.wb_filteredInteractions];
                // Filter by relation type
                if (selectedRelationType) {
                    filteredInteractions = filteredInteractions.filter(interaction => {
                        const isMatch = interaction.type === selectedRelationType;
                        return isMatch;
                    });
                }
                if (selectedLabels) {
                    filteredInteractions = filteredInteractions.filter(interaction => {
                        const isMatch = interaction.label === selectedLabels;
                        return isMatch;
                    });
                }
                // Additional filtering for leads
                if (selectedRelationType === 'leads') {
                    if (selectedSource) {
                        filteredInteractions = filteredInteractions.filter(interaction => {
                            const isMatch = interaction.source === selectedSource;
                            return isMatch;
                        });
                    }
                    if (selectedStatus) {
                        filteredInteractions = filteredInteractions.filter(interaction => {
                            const isMatch = interaction.status === selectedStatus;
                            return isMatch;
                        });
                    }
                }
                // Additional filtering for contacts
                if (selectedRelationType === 'contacts') {
                    if (selectedGroup) {
                        filteredInteractions = filteredInteractions.filter(interaction => {
                            // Check if any group in the array has the matching groupid
                            const hasMatchingGroup = interaction.group && Array.isArray(interaction.group) &&
                                interaction.group.some(group => group.groupid === selectedGroup);
                            return hasMatchingGroup;
                        });
                    }
                }
                // Apply agent filtering overall if selectedAgent exists
                if (selectedAgent) {
                    filteredInteractions = filteredInteractions.filter(interaction => {
                        // Check if agent_id exists and is an array
                        const isMatch = interaction.agent && Array.isArray(interaction.agent.agent_id) &&
                            interaction.agent.agent_id.includes(selectedAgent);
                        return isMatch;
                    });
                }
                // Assign filtered interactions to displayed interactions
                this.wb_displayedInteractions = filteredInteractions;

                if (this.wb_displayedInteractions.length === 0) {
                    this.noResultsMessage = `<div class="flex justify-center items-center"><p class="text-gray-500 text-lg font-medium">No chat's found for the selected filters.</p></div>`;
                } else {
                    this.noResultsMessage = '';
                }

                if (this.wb_displayedInteractions.length !== 0) {
                    this.labelName = [];
                    this.labelColor = [];
                    filteredInteractions.forEach(interaction => {
                        const matchingLabel = this.wb_requireData.custom_labels.find(label => label.id == interaction.label);
                        let validName = (matchingLabel) ? matchingLabel.label : matchingLabel;
                        let validColor = (matchingLabel) ? matchingLabel.color : matchingLabel;
                        this.labelName.push(validName);
                        this.labelColor.push(validColor);
                    });
                }
            },

            wb_handleItemClick(menu, submenu = null) {
                const input_msg = this.wb_newMessage;
                this.isLoading = true;
                $.ajax({
                    url: '<?= site_url('whatsbot/ai_response'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        menu: menu,
                        submenu: submenu,
                        input_msg: input_msg,
                    },
                    success: (response) => {
                        if (response.status === false) {
                            alert_float('danger', response.message);
                        } else {
                            this.wb_newMessage = response.message || input_msg;
                            this.$nextTick(() => {
                                const input = this.$refs.inputField;
                                input.focus();
                            });
                        }
                        this.isLoading = false;
                    },
                })
            },
            ucfirst(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            },
            toggleEmojiPicker() {
                this.wb_showEmojiPicker = !this.wb_showEmojiPicker;
                if (this.wb_showEmojiPicker) {
                    this.initEmojiPicker();
                } else {
                    this.removeEmojiPicker();
                }
            },
            initEmojiPicker() {
                const container = document.getElementById('emoji-picker-container');
                container.innerHTML = '';
                const pickerOptions = {
                    onEmojiSelect: (emoji) => {
                        this.wb_newMessage += emoji.native;
                    }
                };
                const picker = new EmojiMart.Picker(pickerOptions);
                container.appendChild(picker);
                const input = document.getElementById('wb_newMessage');
                const rect = input.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                document.addEventListener('click', this.handleClickOutside);
            },
            removeEmojiPicker() {
                const container = this.$refs.emojiPickerContainer;
                if (container) {
                    container.innerHTML = '';
                }
                document.removeEventListener('click', this.handleClickOutside);
            },
            handleClickOutside(event) {
                const emojiContainer = this.$refs.emojiPickerContainer;
                if (
                    (emojiContainer && !emojiContainer.contains(event.target) && !event.target.closest('#emoji_btn'))
                ) {
                    this.wb_showEmojiPicker = false;
                    this.removeEmojiPicker();
                    document.removeEventListener('click', this.handleClickOutside);
                }
            },
            toggleAttachmentOptions() {
                this.showAttachmentOptions = !this.showAttachmentOptions;
                this.initMap();

            },
            parsedProducts(cart) {
                try {
                    if (!cart) return [];

                    // If already array
                    if (Array.isArray(cart)) return cart;

                    return JSON.parse(cart);
                } catch (e) {
                    return [];
                }
            },
            calculateTotal(cart) {
                let total = 0;

                try {
                    let items = Array.isArray(cart) ? cart : JSON.parse(cart || '[]');

                    items.forEach(i => {
                        total += parseFloat(i.total || (i.qty * i.price)) || 0;
                    });

                } catch (e) {}

                return total.toFixed(2);
            },
        },

        watch: {

            wb_searchText() {
                this.wb_searchInteractions();
            },
            wb_displayedInteractions(newInteractions) {
                newInteractions.forEach(interaction => {
                    const previousCount = this.previousCounts[interaction.id] || 0;
                    const currentCount = this.wb_countUnreadMessages(interaction.id);

                    if (currentCount > previousCount) {
                        this.playNotificationSound();
                    }
                    this.$set(this.previousCounts, interaction.id, currentCount);
                });
            },
            wb_newMessage(newValue) {
                // Check if the message starts with a slash
                if (newValue.startsWith('/')) {
                    // Activate slash command mode
                    this.slashCommandActive = true;
                    // Extract query text after the slash
                    this.slashCommandQuery = newValue.substring(1);
                    // Reset selected index
                    this.selectedReplyIndex = -1;
                } else {
                    // Deactivate slash command mode if no slash at beginning
                    this.slashCommandActive = false;
                }

                // Call existing search text handler if you have one
                if (this.wb_searchText) {
                    this.wb_searchInteractions();
                }
            },

        },
        mounted() {

            // Add event listener to hide the dropdown when clicking outside
            window.addEventListener('mouseup', (event) => {
                var pol = document.getElementById('all_atech');
                var canned = document.getElementById('canned_replies');
                var slash = document.getElementById('slash_command_replies');
                if (pol) {
                    // Check if the clicked element is outside the attachment options and the button
                    if (event.target !== pol && !pol.contains(event.target) && !event.target.closest('button')) {
                        this.showAttachmentOptions = false; // Hide the dropdown
                    }
                }
                if (canned) {
                    // Check if the clicked element is outside the attachment options and the button
                    if (event.target !== canned && !canned.contains(event.target) && !event.target.closest('button')) {
                        this.cannedRepliesVisible = false; // Hide the dropdown
                    }
                }
                if (slash) {
                    // Check if the clicked element is outside the slash command dropdown
                    if (event.target !== slash && !slash.contains(event.target) &&
                        !event.target.matches('#wb_newMessage') &&
                        !event.target.closest('#wb_newMessage')) {
                        this.closeSlashCommand(); // Hide the dropdown
                    }
                }
            });
        },
        created() {
            // New paginated chat board implementaton
            this.getChats().then(() => {
                // After fetching data, initialize Pusher to listen for updates
                this.initializePusher();
                this.handlerequiredata();
            });
            this.darkMode = localStorage.getItem('darkMode') === 'true';
        },
        computed: {
            overdueAlert() {
                const lastMsgTime = this.wb_selectedinteraction.last_msg_time;
                const timezone = "<?= get_option('default_timezone'); ?>";
                if (lastMsgTime) {
                    const currentDate = new Date(new Date().toLocaleString("en-US", {
                        timeZone: timezone
                    }));
                    const messageDate = new Date(lastMsgTime);
                    const diffInHours = Math.floor((currentDate - messageDate) / (1000 * 60 * 60));

                    if (diffInHours >= 24) {
                        return `
                            <div class="flex items-center bg-amber-100 dark:bg-gray-700 dark:text-yellow-400 w-full text-amber-700 p-2 rounded relative " role="alert">
                            <i class="fas fa-exclamation-triangle mr-2 fa-xl text-amber-700 dark:text-yellow-400"></i>
                            <span class="block sm:inline"><span class="font-semibold text-amber-700 dark:text-yellow-400">24 hours limit</span> WhatsApp blocks messages 24 hours after the last contact, but template messages can still be sent.</span>
                        </div>`;
                    }
                }
                return null;
            },
            slashFilteredReplies() {
                if (!this.slashCommandQuery) {
                    return this.cannedReplies;
                }

                const query = this.slashCommandQuery.toLowerCase();
                return this.cannedReplies.filter(reply => {
                    return (
                        reply.title.toLowerCase().includes(query) ||
                        reply.description.toLowerCase().includes(query)
                    );
                });
            },
            wb_selectedInteraction() {
                return this.wb_selectedinteractionIndex !== null ? this.interactions[this.wb_selectedinteractionIndex] : null;
            },
            wb_showSendButton() {
                return this.wb_imageAttachment || this.wb_videoAttachment || this.wb_documentAttachment || this.wb_newMessage.trim();
            },

            isButtonEnabled() {
                return this.wb_newMessage.trim().length > 0;
            },
            wb_uniqueWaNos() {
                // Create a Set to store unique wa_no values
                const wb_uniqueWaNos = new Set();
                // Filter out interactions with duplicate wa_no values
                return this.interactions.filter(interaction => {
                    if (!wb_uniqueWaNos.has(interaction.wa_no)) {
                        wb_uniqueWaNos.add(interaction.wa_no);

                        return true;
                    }
                    return false;
                });
            },
            filteredLanguages() {
                return this.languages.filter(lang => lang.toLowerCase().includes(this.searchQuery.toLowerCase()));
            },
        },
    });
</script>