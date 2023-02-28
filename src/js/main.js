/**
 * ADD CATEGORY FUNCTIONALITY
 */
const categories = [
    { id: 1001, title: "Engineering" },
    { id: 1002, title: "Creepypasta" },
    { id: 1003, title: "Documentary" },
    { id: 1004, title: "Games" },
    { id: 1005, title: "Music" },
    { id: 1006, title: "Psychology" },
    { id: 1007, title: "Streaming" },
];

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

function add_category(evt) {
    const node = evt.target.parentNode.getElementsByTagName("select")[0];

    associate_category(node.attributes["js-channel-id-hash"]?.nodeValue , node.selectedOptions[0].value, node.selectedOptions[0].label);
}

function associate_category(item_id, category_id, category_title) {
    var data = {
        category_id: category_id,
        category_title: category_title,
        item_id: item_id
    }
    
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

function update_categories() {
    var selector_nodes = document.querySelectorAll("[name=add_category]");
    selector_nodes.forEach(function (selector_node) {
        var selected_index = selector_node.selectedIndex;

        if(selected_index > 0) {
            associate_category(selector_node.getAttribute("js-channel-id-hash"), selector_node.value, selector_node.options[selected_index].label);
        }
    })
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


    Array.from(document.querySelectorAll("[js-data-src]")).forEach((el) => {
        categories.forEach(function (category) {
            let option = document.createElement("option");
            option.text = category.title;
            option.value = category.id;
            el.appendChild(option);
        });
    });

    Array.from(document.querySelectorAll("[js-channel-id]")).forEach((el) => {
        el.setAttribute(
            "js-channel-id-hash",
            MD5(el.getAttribute("js-channel-id"))
        );
    });


    document.getElementsByName("category_submit").forEach(function (el) {
        el.addEventListener("click", update_categories);
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
