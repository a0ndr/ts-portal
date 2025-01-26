<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tspace auth portal</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="root" style="background-color: rgb(49, 49, 49); width: 100vw; height: 100vh;">
        <h1 class="name">tspace</h1>
        <div class="content loading" id="loading">
            <p class="icon"><span class="loader"></span></p>
        </div>

        <div class="confirmation hidden" id="authConfirmation">
            <h2 class="title">Confirm</h2>
            <p class="details">Are you sure you want to authorize?</p>
            <div id="actions">
                <button id="authCancel" class="secondary">Cancel</button>
                <button id="authConfirm" class="primary">Authorize</button>
            </div>
        </div>

        <div class="confirmation hidden" id="closed">
            <h2 class="title">Canceled</h2>
            <p class="details">You can now close this window.</p>
        </div>

        <div class="content hidden" id="processing">
            <p class="icon"><span class="loader"></span></p>
            <h1 class="title">Processing</h1>
            <p class="details">
                We are processing your request. You will be redirected in a moment.
            </p>
        </div>

        <div class="content hidden" id="success">
            <p class="icon"><i class="fa-solid fa-circle-check" style="color: #8ff0a4;"></i></p>
            <h2 class="title">Success</h2>
            <p class="details">You can close this window.</p>
        </div>

        <div class="confirmation hidden" id="paymentConfirmation">
            <p class="icon"><i class="fa-solid fa-circle-check" style="color: #8ff0a4;"></i></p>
            <h2 class="title">Payment created</h2>
            <p class="details">The payment request has succeeded. You can close this window.</p>
        </div>

        <div class="content hidden" id="errorContent">
            <p class="icon"><i class="fa-solid fa-circle-xmark" style="color: #f66151;"></i></p>
            <h2 class="title">Error</h2>
            <p class="details" id="errorMessage"></p>
            <p id="showDetails" role="button" class="hidden">Show details</p>
            <p class="details hidden" id="errorWrapper">
                <code id="error"></code>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script>
        function showError(message, details = "") {
            $("#errorContent").show();
            $("#errorMessage").text(message);
            if (details != "") {
                $("#showDetails").removeClass("hidden");
                $("#error").text(details);
            }
        }

        let showDetails = false;
        $("#showDetails").click(() => {
            if (!showDetails) {
                $("#errorWrapper").removeClass("hidden");
                $("#showDetails").text("Hide details");
                showDetails = true;
            } else {
                $("#errorWrapper").addClass("hidden");
                $("#showDetails").text("Show details");
                showDetails = false;
            }
        })

        let code, state;
        let authorizationId, result;

        $(document).ready(() => {
            $("#loading").hide();

            let params = new URLSearchParams(document.location.search);
            if (params.has('code') && params.has('state')) {
                code = params.get('code');
                state = params.get('state');
                $("#authConfirmation").show();
            } else if (params.has('authorizationId') && params.has('result')) {
                authorizationId = params.get('authorizationId');
                result = params.get('ok');
                $("#paymentConfirmation").show();
            } else {
                showError("Cannot determine the intended action because the provided query parameters are conflicting or incomplete.");
            }
        })

        $("#authCancel").click(() => {
            $("#authConfirmation").hide();
            $("#closed").show();
        });
        $("#authConfirm").click(() => {
            $("#authConfirmation").hide();
            $("#processing").show();

            try {
                let rsp = $.ajax({
                    url: '<?=getenv("ADMIN_API_URL")?>/callback',
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify({
                        code, state
                    })
                });

                if (rsp.status != 204) {
                    $("#processing").hide();
                    showError("An error occurred while processing your request. Please try again or contact the administrator.", rsp.responseJSON.error);
                    return;
                }

                $("#processing").hide();
                $("#success").show();
            } catch (e) {
                $("#processing").hide();
                showError("An error occurred while processing your request. Please try again or contact the administrator.", e);
            }
        });
    </script>
</body>

</html>