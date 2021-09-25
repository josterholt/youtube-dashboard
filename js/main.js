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
            option.value = MD5(category);
            el.appendChild(option);
        });
    });

    Array.from(document.querySelectorAll("[js-channel-id]")).forEach((el) => {
        el.setAttribute(
            "js-channel-id-hash",
            MD5(el.getAttribute("js-channel-id"))
        );
    });

    function add_category(evt) {
        const node = evt.target.parentNode.getElementsByTagName("select")[0];

        const data = {
            category_id: node.selectedOptions[0].value,
            category_title: node.selectedOptions[0].label,
            item_id: node.attributes["js-channel-id-hash"]?.nodeValue,
        };

        fetch("/api/categories", { method: "POST", body: JSON.stringify(data) })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                console.log(data);
                location.reload();
            })
            .catch(function (error) {
                console.log(error);
            });
    }

    document.getElementsByName("category_submit").forEach(function (el) {
        el.addEventListener("click", add_category);
    });

    document
        .getElementsByName("category_select")[0]
        .addEventListener("change", function (evt) {
            document.location.search = "category=" + evt.target.value;
        });
    /**
     * END ADD CATEGORY FUNCTIONALITY
     */
};
