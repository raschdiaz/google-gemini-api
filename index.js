console.log(environment);
// Example of a direct fetch request (simplified, without full error handling)
async function getModelsList() {
    const API_KEY = environment.API_KEY;
    const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models?pageSize=1000&key=${API_KEY}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        /*body: JSON.stringify({
            contents: [{ parts: [{ text: "Tell me a joke." }] }]
        })*/
    });
    const data = await response.json();
    console.log(data);
    select = document.getElementById('model');
    data['models'].forEach(model => {
        var opt = document.createElement('option');
        opt.value = model['name'];
        opt.innerHTML = model['name'];
        select.appendChild(opt);
    });
    document.getElementById("submit").disabled = false;
    select.value = "models/gemini-2.5-flash";
}

async function gemini25Pro(model, question, fileBase64 = null, fileMimeType = null) {
    const API_KEY = environment.API_KEY;
    const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/${model}:generateContent`, {
        method: 'POST',
        headers: {
            'x-goog-api-key': API_KEY,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            contents: [
                {
                    parts: [
                        {
                            text: question
                        },
                        (fileBase64 && {
                            inlineData: {
                                mimeType: fileMimeType,
                                data: fileBase64
                            }
                        })
                    ]
                }
            ]
        })
    });
    const data = await response.json();
    console.log(data);
    if (data.error) {
        document.getElementById("response").innerHTML = "Error: " + data.error.message;
    } else {
        document.getElementById("response").innerHTML = data.candidates[0].content.parts[0].text;
    }
    document.getElementById("submit").innerHTML = "Enviar";
    document.getElementById("submit").disabled = false;
}

//callGeminiApiDirectly();