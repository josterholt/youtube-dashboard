/**
 * ADD CATEGORY FUNCTIONALITY
 */
const categories = [
    { id: 1016, title: "Career" },
    { id: 1002, title: "Creepypasta" },
    { id: 1003, title: "Documentary" },
    { id: 1001, title: "Engineering" },
    { id: 1018, title: "Entertainment" },
    { id: 1004, title: "Games" },
    { id: 1013, title: "Game Development" },
    { id: 1014, title: "Marketing" },
    { id: 1005, title: "Music" },
    { id: 1012, title: "Movies" },
    { id: 1015, title: "Organization" },
    { id: 1006, title: "Psychology" },
    { id: 1017, title: "Science" },
    { id: 1011, title: "Self-Help" },
    { id: 1009, title: "Stories" },
    { id: 1007, title: "Streaming" },
    { id: 1010, title: "Technical" },
    { id: 1008, title: "Unfiction" }
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

async function associate_category(item_id, category_id, category_title) {
    var data = {
        category_id: category_id,
        category_title: category_title,
        item_id: item_id
    }
    
    return fetch("/api/categories", { method: "POST", body: JSON.stringify(data) })
}

function update_categories() {
    var selector_nodes = document.querySelectorAll("[name=add_category]");

    var associate_calls = [];
    selector_nodes.forEach(function (selector_node) {
        var selected_index = selector_node.selectedIndex;

        if(selected_index > 0) {
            associate_calls.push(associate_category(selector_node.getAttribute("js-channel-id-hash"), selector_node.value, selector_node.options[selected_index].label));
        }
    })

    Promise.all(associate_calls).then(function (values) {
        console.log("Sync complete, reloading page...")
        location.reload()
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
