function toggleDisplay(evt, channelId) {
    const el = document.getElementById("js-video-list-" + channelId);
    if (!el) {
        return;
    }

    const button_el = evt.target;

    if (el.style.display === "none") {
        el.style.display = "block";
        button_el.innerHTML = "Hide Videos";
    } else {
        el.style.display = "none";
        button_el.innerHTML = "Show Videos";
    }
}

window.onload = function (event) {
    /**
     * SHOW/HIDE VIDEOS FUNCTIONALITY
     */
    Array.from(document.getElementsByClassName("js-video-list-toggle")).forEach(
        (button_el) => {
            const channelId = button_el.getAttribute("js-channelId");
            button_el.addEventListener("click", function (evt) {
                toggleDisplay(evt, channelId);
            });
        }
    );
    /**
     * END SHOW/HIDE VIDEOS FUNCTIONALITY
     */

    /**
     * ADD CATEGORY FUNCTIONALITY
     */
    const categories = [
        "Engineering",
        "Creepypasta",
        "Documentary",
        "Games",
        "Music",
        "Psychology",
        "Streaming",
    ];

    Array.from(document.querySelectorAll("[js-data-src]")).forEach((el) => {
        categories.forEach(function (category) {
            let option = document.createElement("option");
            option.text = category;
            option.value = category;
            el.appendChild(option);
        });
    });

    function stringToHash(string) {
        var hash = 0;

        if (string.length == 0) return hash;

        for (i = 0; i < string.length; i++) {
            char = string.charCodeAt(i);
            hash = (hash << 5) - hash + char;
            hash = hash & hash;
        }

        return hash;
    }

    function add_category(evt) {
        const node = evt.target.parentNode.getElementsByTagName("select")[0];
        const channel_id = node.attributes["js-channel-id"]?.nodeValue;
        const channel_hash = stringToHash(channel_id);
        const category_hash = stringToHash(node.value);

        fetch(
            `/api/categories?categoryID=${category_hash}&itemID=${channel_hash}`
        )
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                console.log(data);
            });
    }

    document.getElementsByName("category_submit").forEach(function (el) {
        el.addEventListener("click", add_category);
    });
    /**
     * END ADD CATEGORY FUNCTIONALITY
     */
};
