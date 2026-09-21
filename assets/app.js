document.addEventListener("DOMContentLoaded", () => {

    const deviceSelect = document.getElementById("device");
    const regionSelect = document.getElementById("region");
    const versionSelect = document.getElementById("version");
    const resolveBtn = document.getElementById("resolveBtn");
    const results = document.getElementById("results");

    let releases = [];
    let selectedDevice = "";

    let devicePicker = null;


    // =========================================================
    // HELPERS
    // =========================================================

    function escapeHTML(value) {
        if (value === null || value === undefined) {
            return "";
        }

        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    function formatSize(bytes) {

        if (!bytes || Number(bytes) <= 0) {
            return "Unknown";
        }

        const units = ["B", "KB", "MB", "GB", "TB"];

        let size = Number(bytes);
        let index = 0;

        while (
            size >= 1024 &&
            index < units.length - 1
        ) {
            size /= 1024;
            index++;
        }

        return size.toFixed(2) + " " + units[index];
    }


    // =========================================================
    // BRAND
    // =========================================================

    function getBrand(device) {

        const name = String(device || "")
            .toLowerCase()
            .trim();


        if (name.includes("oneplus") || name.includes("one plus")) {
            return "OnePlus";
        }

        if (name.includes("oppo")) {
            return "OPPO";
        }

        if (name.includes("realme")) {
            return "Realme";
        }

        if (name.includes("xiaomi")) {
            return "Xiaomi";
        }

        if (name.includes("redmi")) {
            return "Redmi";
        }

        if (name.includes("poco")) {
            return "POCO";
        }

        if (name.includes("samsung")) {
            return "Samsung";
        }

        if (name.includes("vivo")) {
            return "Vivo";
        }

        if (name.includes("iqoo")) {
            return "iQOO";
        }

        if (
            name.includes("motorola") ||
            name.includes("moto")
        ) {
            return "Motorola";
        }

        if (
            name.includes("google") ||
            name.includes("pixel")
        ) {
            return "Google";
        }

        if (name.includes("asus")) {
            return "ASUS";
        }

        if (name.includes("nothing")) {
            return "Nothing";
        }

        if (name.includes("honor")) {
            return "HONOR";
        }

        if (name.includes("huawei")) {
            return "Huawei";
        }

        if (name.includes("sony")) {
            return "Sony";
        }

        if (name.includes("tecno")) {
            return "Tecno";
        }

        if (name.includes("infinix")) {
            return "Infinix";
        }

        return "Other";
    }


    // =========================================================
    // SERIES
    // =========================================================

    function getSeries(device, brand) {

        const name = String(device || "")
            .toLowerCase()
            .trim();


        if (brand === "OnePlus") {

            if (name.includes("nord")) {
                return "Nord";
            }

            if (name.includes("ace")) {
                return "Ace";
            }

            if (name.includes("pad")) {
                return "Pad";
            }

            if (
                name.includes("watch") ||
                name.includes("buds")
            ) {
                return "Wearables";
            }

            return "Main Series";
        }


        if (brand === "OPPO") {

            if (name.includes("find")) {
                return "Find Series";
            }

            if (name.includes("reno")) {
                return "Reno Series";
            }

            if (/\ba\d+/i.test(name)) {
                return "A Series";
            }

            if (/\bk\d+/i.test(name)) {
                return "K Series";
            }

            return "Other";
        }


        if (brand === "Realme") {

            if (name.includes("gt")) {
                return "GT Series";
            }

            if (name.includes("narzo")) {
                return "Narzo Series";
            }

            if (/\bc\d+/i.test(name)) {
                return "C Series";
            }

            return "Other";
        }


        if (brand === "Xiaomi") {

            if (name.includes("mix")) {
                return "MIX Series";
            }

            if (name.includes("ultra")) {
                return "Ultra Series";
            }

            if (name.includes("t")) {
                return "T Series";
            }

            return "Xiaomi Series";
        }


        if (brand === "Redmi") {

            if (name.includes("note")) {
                return "Note Series";
            }

            if (name.includes("k")) {
                return "K Series";
            }

            return "Redmi Series";
        }


        if (brand === "POCO") {

            if (/\bf\d+/i.test(name)) {
                return "F Series";
            }

            if (/\bx\d+/i.test(name)) {
                return "X Series";
            }

            if (/\bm\d+/i.test(name)) {
                return "M Series";
            }

            return "POCO Series";
        }


        if (brand === "Samsung") {

            if (name.includes("galaxy s")) {
                return "Galaxy S";
            }

            if (name.includes("galaxy a")) {
                return "Galaxy A";
            }

            if (name.includes("galaxy m")) {
                return "Galaxy M";
            }

            if (name.includes("galaxy z")) {
                return "Galaxy Z";
            }

            if (name.includes("galaxy note")) {
                return "Galaxy Note";
            }

            if (name.includes("galaxy tab")) {
                return "Galaxy Tab";
            }

            return "Galaxy";
        }


        if (brand === "Vivo") {

            if (/\bx\d+/i.test(name)) {
                return "X Series";
            }

            if (/\bv\d+/i.test(name)) {
                return "V Series";
            }

            if (/\by\d+/i.test(name)) {
                return "Y Series";
            }

            return "Other";
        }


        if (brand === "iQOO") {

            if (name.includes("neo")) {
                return "Neo Series";
            }

            if (/\bz\d+/i.test(name)) {
                return "Z Series";
            }

            return "iQOO Series";
        }


        if (brand === "Motorola") {

            if (name.includes("edge")) {
                return "Edge Series";
            }

            if (name.includes("razr")) {
                return "Razr Series";
            }

            if (name.includes("moto g")) {
                return "Moto G";
            }

            return "Moto";
        }


        if (brand === "Google") {
            return "Pixel";
        }


        return "Other";
    }


    // =========================================================
    // CREATE DEVICE PICKER
    // =========================================================

    function createDevicePicker() {

        // Hide original select
        deviceSelect.style.display = "none";


        const wrapper = document.createElement("div");

        wrapper.className = "device-picker";


        // -----------------------------------------------------
        // Button
        // -----------------------------------------------------

        const button = document.createElement("button");

        button.type = "button";

        button.className = "device-picker-button";

        button.innerHTML = `
            <span class="device-picker-text">
                Choose a device...
            </span>

            <span class="device-picker-arrow">
                ⌄
            </span>
        `;


        // -----------------------------------------------------
        // Dropdown
        // -----------------------------------------------------

        const dropdown = document.createElement("div");

        dropdown.className = "device-picker-dropdown";


        // -----------------------------------------------------
        // Search
        // -----------------------------------------------------

        const search = document.createElement("div");

        search.className = "device-search";

        search.innerHTML = `
            <input
                type="text"
                placeholder="Search device..."
                autocomplete="off"
            >
        `;


        // -----------------------------------------------------
        // List
        // -----------------------------------------------------

        const list = document.createElement("div");

        list.className = "device-list";


        dropdown.appendChild(search);

        dropdown.appendChild(list);

        wrapper.appendChild(button);

        wrapper.appendChild(dropdown);


        // Put custom picker before hidden select
        deviceSelect.parentNode.insertBefore(
            wrapper,
            deviceSelect
        );


        devicePicker = {
            wrapper,
            button,
            dropdown,
            searchInput: search.querySelector("input"),
            list
        };


        // =====================================================
        // OPEN / CLOSE
        // =====================================================

        button.addEventListener("click", (event) => {

            event.preventDefault();

            event.stopPropagation();


            const isOpen =
                wrapper.classList.contains("open");


            if (isOpen) {

                closeDevicePicker();

            } else {

                openDevicePicker();

            }

        });


        // Do NOT close when clicking inside
        dropdown.addEventListener("click", (event) => {

            event.stopPropagation();

        });


        // Search must stay open
        devicePicker.searchInput.addEventListener(
            "click",
            event => {

                event.stopPropagation();

            }
        );


        devicePicker.searchInput.addEventListener(
            "input",
            event => {

                event.stopPropagation();

                renderDeviceList(
                    event.target.value
                );

                openDevicePicker();

            }
        );


        // Close only when clicking outside
        document.addEventListener(
            "click",
            event => {

                if (!wrapper.contains(event.target)) {

                    closeDevicePicker();

                }

            }
        );


        renderDeviceList();

    }


    // =========================================================
    // OPEN
    // =========================================================

    function openDevicePicker() {

        if (!devicePicker) {
            return;
        }

        devicePicker.wrapper.classList.add("open");

    }


    // =========================================================
    // CLOSE
    // =========================================================

    function closeDevicePicker() {

        if (!devicePicker) {
            return;
        }

        devicePicker.wrapper.classList.remove("open");

    }


    // =========================================================
    // RENDER DEVICE LIST
    // =========================================================

    function renderDeviceList(searchText = "") {

        const list = devicePicker.list;

        list.innerHTML = "";


        const search =
            String(searchText)
                .toLowerCase()
                .trim();


        const structure = {};


        // -----------------------------------------------------
        // Build hierarchy
        // -----------------------------------------------------

        releases.forEach(item => {

            if (!item.device) {
                return;
            }


            const device =
                String(item.device).trim();


            const brand =
                getBrand(device);


            const series =
                getSeries(
                    device,
                    brand
                );


            // Search
            if (
                search &&
                !device.toLowerCase().includes(search) &&
                !brand.toLowerCase().includes(search) &&
                !series.toLowerCase().includes(search)
            ) {
                return;
            }


            if (!structure[brand]) {
                structure[brand] = {};
            }


            if (!structure[brand][series]) {
                structure[brand][series] = new Set();
            }


            structure[brand][series].add(device);

        });


        const brands =
            Object.keys(structure).sort(
                (a, b) =>
                    a.localeCompare(b)
            );


        if (!brands.length) {

            list.innerHTML = `
                <div class="device-no-results">
                    No devices found
                </div>
            `;

            return;
        }


        // =====================================================
        // BRANDS
        // =====================================================

        brands.forEach(brand => {

            const brandDevices =
                new Set(
                    releases
                        .filter(
                            item =>
                                getBrand(item.device) === brand
                        )
                        .map(
                            item => item.device
                        )
                        .filter(Boolean)
                );


            const brandRow =
                document.createElement("div");

            brandRow.className =
                "device-brand";


            brandRow.innerHTML = `
                <div class="device-brand-left">

                    <span class="device-chevron">
                        ›
                    </span>

                    <strong>
                        ${escapeHTML(brand)}
                    </strong>

                </div>

                <span class="device-count">
                    ${brandDevices.size}
                </span>
            `;


            const seriesContainer =
                document.createElement("div");

            seriesContainer.className =
                "device-series-container";


            // -------------------------------------------------
            // Brand click
            // -------------------------------------------------

            brandRow.addEventListener(
                "click",
                event => {

                    event.preventDefault();

                    event.stopPropagation();


                    brandRow.classList.toggle(
                        "expanded"
                    );


                    seriesContainer.classList.toggle(
                        "expanded"
                    );

                }
            );


            list.appendChild(
                brandRow
            );


            list.appendChild(
                seriesContainer
            );


            // =================================================
            // SERIES
            // =================================================

            const seriesNames =
                Object.keys(
                    structure[brand]
                ).sort(
                    (a, b) =>
                        a.localeCompare(b)
                );


            seriesNames.forEach(series => {

                const models =
                    Array.from(
                        structure[brand][series]
                    ).sort(
                        (a, b) =>
                            a.localeCompare(b)
                    );


                const seriesRow =
                    document.createElement("div");

                seriesRow.className =
                    "device-series";


                const seriesHeader =
                    document.createElement("div");

                seriesHeader.className =
                    "device-series-header";


                seriesHeader.innerHTML = `

                    <span class="device-chevron">
                        ›
                    </span>

                    <span>
                        ${escapeHTML(series)}
                    </span>

                    <span class="device-series-count">
                        ${models.length}
                    </span>

                `;


                const modelContainer =
                    document.createElement("div");

                modelContainer.className =
                    "device-model-container";


                seriesRow.appendChild(
                    seriesHeader
                );

                seriesRow.appendChild(
                    modelContainer
                );


                seriesContainer.appendChild(
                    seriesRow
                );


                // -------------------------------------------------
                // Series click
                // -------------------------------------------------

                seriesHeader.addEventListener(
                    "click",
                    event => {

                        event.preventDefault();

                        event.stopPropagation();


                        seriesRow.classList.toggle(
                            "expanded"
                        );


                        modelContainer.classList.toggle(
                            "expanded"
                        );

                    }
                );


                // =================================================
                // MODELS
                // =================================================

                models.forEach(model => {

                    const modelRow =
                        document.createElement("div");


                    modelRow.className =
                        "device-model";


                    modelRow.textContent =
                        model;


                    modelRow.addEventListener(
                        "click",
                        event => {

                            event.preventDefault();

                            event.stopPropagation();


                            selectDevice(
                                model
                            );

                        }
                    );


                    modelContainer.appendChild(
                        modelRow
                    );

                });

            });

        });

    }


    // =========================================================
    // SELECT DEVICE
    // =========================================================

    function selectDevice(device) {

        // THIS IS IMPORTANT
        // Store selected device separately
        selectedDevice = String(device);


        // Update hidden select too
        deviceSelect.value = selectedDevice;


        // Update visible button
        devicePicker
            .button
            .querySelector(
                ".device-picker-text"
            )
            .textContent =
            selectedDevice;


        // Clear search
        devicePicker.searchInput.value = "";


        // Close dropdown ONLY after actual model selection
        closeDevicePicker();


        // Load regions
        loadRegions(
            selectedDevice
        );

    }


    // =========================================================
    // LOAD REGIONS
    // =========================================================

    function loadRegions(device) {

        resetVersion();


        const regions =
            [
                ...new Set(

                    releases
                        .filter(
                            item =>
                                String(item.device) ===
                                String(device)
                        )
                        .map(
                            item =>
                                item.region
                        )
                        .filter(Boolean)

                )
            ];


        regions.sort(
            (a, b) =>
                String(a).localeCompare(
                    String(b)
                )
        );


        regionSelect.innerHTML = "";


        const first =
            document.createElement("option");


        first.value = "";

        first.textContent =
            regions.length
                ? "Choose a region..."
                : "No regions available";


        regionSelect.appendChild(
            first
        );


        regions.forEach(region => {

            const option =
                document.createElement("option");


            option.value =
                String(region);


            option.textContent =
                String(region);


            regionSelect.appendChild(
                option
            );

        });


        regionSelect.disabled =
            regions.length === 0;

    }


    // =========================================================
    // REGION CHANGE
    // =========================================================

    regionSelect.addEventListener(
        "change",
        () => {

            // IMPORTANT:
            // Do NOT use deviceSelect.value here.
            // The custom picker uses selectedDevice.

            const device =
                selectedDevice;


            const region =
                regionSelect.value;


            resetVersion();


            if (!device || !region) {
                return;
            }


            const versions =
                releases.filter(
                    item =>

                        String(item.device) ===
                        String(device)

                        &&

                        String(item.region) ===
                        String(region)
                );


            // Latest first
            versions.sort(
                (a, b) => {

                    if (
                        a.is_latest &&
                        !b.is_latest
                    ) {
                        return -1;
                    }


                    if (
                        !a.is_latest &&
                        b.is_latest
                    ) {
                        return 1;
                    }


                    return String(
                        b.version || ""
                    ).localeCompare(
                        String(
                            a.version || ""
                        )
                    );

                }
            );


            versionSelect.innerHTML = "";


            const first =
                document.createElement("option");


            first.value = "";


            first.textContent =
                versions.length
                    ? "Choose a version..."
                    : "No versions available";


            versionSelect.appendChild(
                first
            );


            versions.forEach(item => {

                const option =
                    document.createElement(
                        "option"
                    );


                option.value =
                    String(item.id);


                option.textContent =
                    item.version ||
                    "Unknown version";


                versionSelect.appendChild(
                    option
                );

            });


            versionSelect.disabled =
                versions.length === 0;


            // Make sure the version select is clickable
            versionSelect.style.pointerEvents =
                "auto";

    });


    // =========================================================
    // RESOLVE BUTTON
    // =========================================================

    resolveBtn.addEventListener(
        "click",
        () => {

            const id =
                versionSelect.value;


            if (!selectedDevice) {

                showError(
                    "Please select a device first."
                );

                return;
            }


            if (!regionSelect.value) {

                showError(
                    "Please select a region."
                );

                return;
            }


            if (!id) {

                showError(
                    "Please select a firmware version."
                );

                return;
            }


            const selected =
                releases.find(
                    item =>
                        String(item.id) ===
                        String(id)
                );


            if (!selected) {

                showError(
                    "Selected firmware could not be found."
                );

                return;
            }


            showFirmware(
                selected
            );

        }
    );


    // =========================================================
    // SHOW FIRMWARE
    // =========================================================

    function showFirmware(item) {

        const brand =
            getBrand(
                item.device
            );


        const series =
            getSeries(
                item.device,
                brand
            );


        const size =
            formatSize(
                item.size
            );


        const md5 =
            item.md5 ||
            "Not available";


        const patch =
            item.security_patch ||
            "Not available";


        const model =
            item.model ||
            "Not available";


        const build =
            item.build_timestamp ||
            "Not available";


        const url =
            item.source_url ||
            "";


        results.innerHTML = `

            <div class="firmware-result">

                <div class="result-header">

                    <div>

                        <div class="eyebrow">
                            FIRMWARE FOUND
                        </div>

                        <h3>
                            ${escapeHTML(item.device)}
                        </h3>

                        <p>
                            ${escapeHTML(item.version)}
                        </p>

                    </div>


                    <span class="status-badge">

                        ${
                            item.is_latest
                                ? "LATEST"
                                : "AVAILABLE"
                        }

                    </span>

                </div>


                <div class="firmware-grid">

                    <div>
                        <span>BRAND</span>
                        <strong>
                            ${escapeHTML(brand)}
                        </strong>
                    </div>


                    <div>
                        <span>SERIES</span>
                        <strong>
                            ${escapeHTML(series)}
                        </strong>
                    </div>


                    <div>
                        <span>DEVICE</span>
                        <strong>
                            ${escapeHTML(item.device)}
                        </strong>
                    </div>


                    <div>
                        <span>MODEL CODE</span>
                        <strong>
                            ${escapeHTML(model)}
                        </strong>
                    </div>


                    <div>
                        <span>REGION</span>
                        <strong>
                            ${escapeHTML(item.region)}
                        </strong>
                    </div>


                    <div>
                        <span>VERSION</span>
                        <strong>
                            ${escapeHTML(item.version)}
                        </strong>
                    </div>


                    <div>
                        <span>SIZE</span>
                        <strong>
                            ${size}
                        </strong>
                    </div>


                    <div>
                        <span>SECURITY PATCH</span>
                        <strong>
                            ${escapeHTML(patch)}
                        </strong>
                    </div>


                    <div>
                        <span>BUILD</span>
                        <strong>
                            ${escapeHTML(build)}
                        </strong>
                    </div>


                    <div>
                        <span>MD5</span>
                        <strong class="hash">
                            ${escapeHTML(md5)}
                        </strong>
                    </div>

                </div>


        <div class="download-actions">

    ${
        url

        ?

        `
    <button
    type="button"
    class="primary download-button"
    data-release-id="${escapeHTML(item.id)}"
    data-source-url="${escapeHTML(item.source_url || '')}"
>
    Download Firmware
</button>
        `

        :

        `
        <button
            type="button"
            class="primary"
            disabled
        >
            Download Link Unavailable
        </button>
        `
    }

</div>

        `;

const downloadButton =
    results.querySelector(".download-button");


if (downloadButton) {

    downloadButton.addEventListener(
        "click",
        async () => {

            downloadButton.disabled = true;

            downloadButton.textContent =
                "Preparing Download...";


            try {

                const response =
                    await fetch(
                        "log_download.php",
                        {
                            method: "POST",

                            headers: {
                                "Content-Type":
                                    "application/json"
                            },

                            body: JSON.stringify({

                                firmware_id:
                                    item.id,

                                device:
                                    item.device,

                                model:
                                    item.model || "",

                                region:
                                    item.region || "",

                                version:
                                    item.version || "",

                                ota_version:
                                    item.ota_version || "",

                                size_bytes:
                                    Number(
                                        item.size || 0
                                    ),

                                source_url:
                                    item.source_url || ""

                            })

                        }
                    );


                const data =
                    await response.json();


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.error ||
                        "Could not record download."
                    );

                }


                // ------------------------------------------------
                // Open original firmware source
                // ------------------------------------------------

const resolverUrl =
    'download.php?download_id=' +
    encodeURIComponent(data.download_id) +
    '&url=' +
    encodeURIComponent(data.source_url);

window.location.href = resolverUrl;


                downloadButton.textContent =
                    "Download Started";


                downloadButton.disabled =
                    false;


            } catch (error) {

                console.error(
                    "Download logging error:",
                    error
                );


                showError(
                    error.message ||
                    "Unable to start download."
                );


                downloadButton.disabled =
                    false;


                downloadButton.textContent =
                    "Download Firmware";

            }

        }
    );

}



    }


    // =========================================================
    // ERROR
    // =========================================================

    function showError(message) {

        results.innerHTML = `






            <div class="error-message">
                ${escapeHTML(message)}
            </div>

        `;

    }


    // =========================================================
    // RESET
    // =========================================================

    window.resetFilters = function () {

        selectedDevice = "";


        if (devicePicker) {

            devicePicker
                .button
                .querySelector(
                    ".device-picker-text"
                )
                .textContent =
                "Choose a device...";


            devicePicker.searchInput.value = "";


            closeDevicePicker();

        }


        deviceSelect.value = "";


        resetRegion();

        resetVersion();


        results.innerHTML = `

            <div class="empty-state">

                <div class="empty-icon">
                    ↓
                </div>

                <h3>
                    No firmware selected
                </h3>

                <p>
                    Choose a device, region and version
                    above to view firmware information.
                </p>

            </div>

        `;

    };


    // =========================================================
    // RESET REGION
    // =========================================================

    function resetRegion() {

        regionSelect.innerHTML = "";


        const option =
            document.createElement("option");


        option.value = "";


        option.textContent =
            "Choose a device first...";


        regionSelect.appendChild(
            option
        );


        regionSelect.disabled =
            true;

    }


    // =========================================================
    // RESET VERSION
    // =========================================================

    function resetVersion() {

        versionSelect.innerHTML = "";


        const option =
            document.createElement("option");


        option.value = "";


        option.textContent =
            "Choose a region first...";


        versionSelect.appendChild(
            option
        );


        versionSelect.disabled =
            true;

    }


    // =========================================================
    // LOAD CATALOG
    // =========================================================

    async function loadCatalog() {

        deviceSelect.style.display =
            "block";


        deviceSelect.innerHTML = `
            <option>
                Loading devices...
            </option>
        `;


        try {

            const response =
                await fetch(
                    "api.php",
                    {
                        method: "GET",
                        cache: "no-store"
                    }
                );


            if (!response.ok) {

                throw new Error(
                    "API HTTP error " +
                    response.status
                );

            }


            const json =
                await response.json();


            if (!json.success) {

                throw new Error(
                    json.error ||
                    "API request failed."
                );

            }


            if (
                !json.data ||
                !Array.isArray(
                    json.data.releases
                )
            ) {

                throw new Error(
                    "Invalid catalog format."
                );

            }


            releases =
                json.data.releases;


            // Remove old custom picker if any
            if (devicePicker) {
                devicePicker.wrapper.remove();
            }


            createDevicePicker();


            resetRegion();

            resetVersion();


        } catch (error) {

            console.error(
                "OTA catalog error:",
                error
            );


            deviceSelect.style.display =
                "block";


            deviceSelect.innerHTML = `
                <option>
                    Catalog unavailable
                </option>
            `;


            showError(
                "Could not load the live OTA catalog."
            );

        }

    }


    // =========================================================
    // START
    // =========================================================

    loadCatalog();

});