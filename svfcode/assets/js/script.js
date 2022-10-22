(function() {
    fetch(ajax_data["url_get_ip"] + "?_wpnonce=" + ajax_data["ajax_nonce"] + "&action=get_api_user_ip", {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        },
    }).then(function(response) {
        return response.json()
    }).then(function(data) {
        if (data.data && data.data.success === true) {
            let input = document.createElement("input");
            input.setAttribute("type", "hidden");
            input.setAttribute("name", "svfcode_spamblock_client_ip");
            input.setAttribute("value", data.data.ip);

            let nonce = document.createElement("input");
            nonce.setAttribute("type", "hidden");
            nonce.setAttribute("name", "_wpnonce");
            nonce.setAttribute("value", data.data.nonce);

            if (document.getElementById("commentform")) {
                document.getElementById("commentform").appendChild(input);
                document.getElementById("commentform").appendChild(nonce);
            }
        }
    }).catch(function(err) {

    });
})()
