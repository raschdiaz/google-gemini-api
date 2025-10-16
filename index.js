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
}

async function gemini25Pro(question) {
    const API_KEY = environment.API_KEY;
    const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent`, {
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
                        }
                    ]
                }
            ]
        })
    });
    const data = await response.json();
    console.log(data);
    document.getElementById("response").innerHTML = data.candidates[0].content.parts[0].text;
}

//callGeminiApiDirectly();