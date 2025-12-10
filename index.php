<!DOCTYPE html>
<html>

<head>
    <title>Google Gemini API</title>
    <meta charset="utf-8" />
</head>

<body>
    <h1>Google Gemini API</h1>
    <form method="post" onsubmit="return handleSubmit(this);">
        <label for="model">Modelo:</label>
        <select id="model" name="model"></select>
        </br>
        <label for="question">Pregunta:</label>
        <input type="textarea" name="question" size="50" />
        </br>
        <label for="file">Archivo:</label>
        <input type="file" name="file" id="file" />
        <button type="button" id="clear" onclick="clearFileField()">Remover Archivo</button>
        </br>
        <label for="file">Archivo URL:</label>
        <input type="textarea" name="file-url" id="file-url" size="50" />
        </br>
        <button id="submit" type="submit">Enviar</button>
    </form>
    <pre id="response"></pre>
    <?php //isset($_POST["base64File"]) ? "<pre>".print_r($_POST)."</pre>" : ""; ?>
    <script type="text/javascript">

        document.getElementById("submit").disabled = true;
        var geminiLoaded = false;
        clearFileField();
        //document.getElementById('file').value = "<?php isset($_POST["base64File"]) ? $_POST["base64File"] : ''; ?>";
        //document.getElementById('file').filename = "<?php isset($_POST["fileName"]) ? $_POST["fileName"] : ''; ?>";

        <?php if(isset($_POST["base64File"])) { ?>
            const file = base64ToFile("<?php echo $_POST["base64File"]; ?>", "<?php echo "file.pdf"//$_POST["fileName"]; ?>", "<?php echo "application/pdf"//$_POST["fileType"]; ?>");
            console.log(file);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('file').files = dataTransfer.files;
        <?php } ?>

        function loadScriptSync(src, callback) {
            var script = document.createElement('script');
            script.src = src;
            script.type = "text/javascript";
            script.async = true;
            script.defer = true;
            script.onload = callback;
            document.getElementsByTagName('body')[0].appendChild(script);
        }

        async function loadEnvironment() {
            const response = await fetch("./environment.json");
            return await response.json();
        }

        // Load environment variables and logic synchronously
        loadEnvironment().then((environment) => {
            window.environment = environment;
            loadScriptSync("index.js", function () {
                geminiLoaded = true;
                getModelsList();
            });
        });

        function handleSubmit(form) {
            // Prevent default form submission
            event.preventDefault();
            document.getElementById("submit").innerHTML = "Enviando...";
            document.getElementById("submit").disabled = true;
            // Access form data (e.g., using FormData API)
            const formData = new FormData(form);

            console.log(formData);

            const file = formData.get("file");
            const fileUrl = formData.get("file-url");
            console.log(file);

            if (file.size > 0) {
                // Create a new FileReader instance
                const reader = new FileReader();

                // Define what happens when the file is finished loading
                reader.onload = function (e) {
                    // The result is a Data URL (e.g., "data:image/png;base64,iVBORw...")
                    const dataURL = e.target.result;
                    console.log("Data URL:", dataURL);
                    const mimeType = dataURL.match(/^data:(.+);base64,/)[1];
                    console.log("mimeType", mimeType);
                    // Optionally, remove the "data:type;base64," prefix to get only the raw Base64 string
                    const base64String = dataURL.replace(/^data:.+;base64,/, '');

                    // Display the result
                    //base64Output.value = base64String;
                    console.log("Full Data URL:", dataURL);
                    console.log("Raw Base64 String:", base64String);

                    if (geminiLoaded) {
                        gemini25Pro(formData.get("model"), formData.get("question"), base64String, mimeType);
                    }
                };

                // Define what happens if an error occurs
                reader.onerror = function (error) {
                    console.error("Error reading file:", error);
                };

                // Read the file's content as a Data URL, which internally Base64 encodes it
                reader.readAsDataURL(file);
            } else if (fileUrl && fileUrl.length > 0) {

                const url = new URL(fileUrl);
                // Get the part of the URL after the domain, e.g., '/path/to/file.png'
                const pathname = url.pathname;
                // Extract the last segment after the last slash
                const filename = pathname.substring(pathname.lastIndexOf('/') + 1);

                downloadFile(fileUrl, filename);

                if (geminiLoaded) {
                    fetch(fileUrl).then((response) => {
                        let arrayBuffer = response.arrayBuffer();
                        const base64String = btoa(
                            new Uint8Array(arrayBuffer)
                                .reduce((data, byte) => data + String.fromCharCode(byte), '')
                        );
                        console.log("base64String from URL:", base64String);
                        //gemini25Pro(formData.get("model"), formData.get("question"), base64String, null,);
                    });
                }
            } else {

                if (geminiLoaded) {
                    gemini25Pro(formData.get("model"), formData.get("question"));
                }

            }

            return false; // Prevent default form submission
        }

        function clearFileField() {
            document.getElementById('file').value = null;
        }

        /*function downloadFile(url, fileName) {
            // Create an anchor element
            const link = document.createElement('a');

            // Set the href attribute to the file's URL
            link.href = url;

            // Set the download attribute to specify the file name for the download
            // This attribute forces the browser to download instead of navigating or opening the file inline
            link.download = fileName;

            // Append the link to the body (necessary for Firefox)
            document.body.appendChild(link);

            // Simulate a click on the link to trigger the download
            link.click();

            // Clean up: remove the link from the document
            document.body.removeChild(link);
        }

        function downloadFile(url, fileName) {
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'blob'; // Get the response as a Blob

            xhr.addEventListener('load', function () {
                if (xhr.status === 200) {
                    console.log('Download finished in memory.');
                    var blob = xhr.response;
                    console.log(blob)
                    // Create a link element to trigger the user's save dialog
                    /*var a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = fileName; // Suggest a file name
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(a.href); // Clean up the object URL
                    *
                    blobToBase64(blob)
                        .then(base64DataUrl => {
                            console.log(base64DataUrl);
                            // Output: data:text/plain;base64,SGVsbG8sIHdvcmxkIQ==

                            // To get just the Base64 string part (without the "data:mime/type;base64," prefix):
                            const base64String = base64DataUrl.split(',')[1];
                            //console.log(base64String);
                            // Output: SGVsbG8sIHdvcmxkIQ==

                            let isBase64URL = isBase64Url(base64DataUrl);
                            console.log('isBase64URL', isBase64URL);
                            let base64 = isBase64URL ? base64urlToBase64(base64DataUrl) : base64Data;
                            console.log('base64', base64)
                            document.getElementById('file').value = base64;
                        })
                        .catch(error => {
                            console.error('Error converting blob:', error);
                        });
                }
            });

            xhr.addEventListener('progress', function (event) {
                if (event.lengthComputable) {
                    var percentComplete = (event.loaded / event.total) * 100;
                    console.log('Download progress: ' + percentComplete + '%');
                    // Update UI (e.g., a progress bar) here
                }
            });

            xhr.addEventListener('error', function (error) {
                console.error('Download failed.', error);
            });

            xhr.open('GET', url);
            xhr.send();
        }

        function blobToBase64(blob) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => {
                    // The result is a data URL (e.g., "data:image/png;base64,...")
                    resolve(reader.result);
                };
                reader.onerror = (error) => {
                    reject(error);
                };
                // Read the blob as a data URL
                reader.readAsDataURL(blob);
            });
        }*/

        function base64ToFile(base64Data, filename, mimeType) {
            const arr = base64Data.split(',');
            const mime = arr[0].match(/:(.*?);/)[1] || mimeType;
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new File([u8arr], filename, { type: mime });
        }

    </script>
</body>

</html>