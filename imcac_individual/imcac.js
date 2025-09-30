// Definición de las preguntas y sus valores
const questions = {
    dim1: [
        {
            text: 'Ante la necesidad de gestionar una instrucción o una tarea que requiere exactitud y seguimiento, ¿qué canal utiliza el equipo?',
            options: [
                { text: 'Se utiliza una herramienta de gestión de proyectos / tareas (ej. Planner, Jira o Trello).', value: 10 },
                { text: 'Se utiliza una herramienta de mensajería instantánea (ej. Teams o Slack).', value: 5 },
                { text: 'Se utiliza el correo electrónico o el WhatsApp.', value: 0 }
            ]
        },
        {
            text: 'Cuando el equipo (varias personas) trabajan en un mismo documento, ¿cuál es el método más común?',
            options: [
                { text: 'Se comparte un enlace a un único documento en la nube (ej. SharePoint, OneDrive, Google Drive).', value: 10 },
                { text: 'Se adjunta el archivo en un correo electrónico o en un mensaje de Teams.', value: 5 },
                { text: 'Se utilizan medios físicos como memorias USB o discos duros externos.', value: 0 }
            ]
        },
        {
            text: 'Para la gestión y el seguimiento de proyectos o tareas, ¿qué herramienta es la más utilizada en el equipo?',
            options: [
                { text: 'Se utiliza un planificador o gestor de tareas (ej. Planner, Trello, Asana, etc.).', value: 10 },
                { text: 'Se utilizan los chats de mensajería instantánea (ej. Teams o Slack).', value: 5 },
                { text: 'Se utilizan principalmente correos electrónicos.', value: 0 }
            ]
        },
        {
            text: 'Cuando se discuten temas importantes, ¿cómo se asegura el equipo de que la información no se pierda en los canales de comunicación?',
            options: [
                { text: 'Se documenta en un repositorio de conocimiento centralizado (ej. SharePoint, Wiki, etc.).', value: 10 },
                { text: 'Se asume que la información permanece en el chat de la conversación.', value: 5 },
                { text: 'La información importante solo se comparte en reuniones.', value: 0 }
            ]
        }
    ],
    dim2: [
        {
            text: 'Cuando un miembro del equipo necesita comunicar una idea o un problema, ¿con qué frecuencia se incluye el contexto, el objetivo y los pasos a seguir de manera clara?',
            options: [
                { text: 'Siempre se incluye un contexto completo, un objetivo claro y una llamada a la acción.', value: 10 },
                { text: 'A veces la comunicación es clara, pero a menudo se omiten detalles importantes.', value: 5 },
                { text: 'La información es a menudo incompleta o requiere aclaraciones constantes.', value: 0 }
            ]
        },
        {
            text: '¿Cómo se maneja la retroalimentación o las solicitudes de cambio en el equipo?',
            options: [
                { text: 'Se utiliza la funcionalidad de comentarios y sugerencias en el mismo documento o herramienta.', value: 10 },
                { text: 'Se envían las sugerencias por correo electrónico o chat.', value: 5 },
                { text: 'Se espera a una reunión para dar la retroalimentación.', value: 0 }
            ]
        },
        {
            text: 'En un mensaje asincrónico, ¿se suele incluir un saludo formal y una despedida?',
            options: [
                { text: 'Sí, los mensajes son concisos y al punto, sin formalidades innecesarias.', value: 10 },
                { text: 'A veces se usan, pero se prioriza la claridad del mensaje.', value: 5 },
                { text: 'No, los mensajes suelen ser conversaciones informales.', value: 0 }
            ]
        },
        {
            text: 'Para recibir una aprobación sobre una tarea, ¿el equipo utiliza herramientas de flujo de trabajo?',
            options: [
                { text: 'Sí, se utilizan flujos de trabajo de aprobación o notificaciones de estado.', value: 10 },
                { text: 'A veces se usan, pero la aprobación suele ser verbal o por un mensaje de chat informal.', value: 5 },
                { text: 'No, las aprobaciones se gestionan fuera de los sistemas de colaboración.', value: 0 }
            ]
        }
    ],
    dim3: [
        {
            text: 'Cuando un miembro del equipo tiene una pregunta, ¿a quién acude para resolverla?',
            options: [
                { text: 'Busca la respuesta en el repositorio de conocimiento o en los documentos del equipo.', value: 10 },
                { text: 'Envía un mensaje o un correo electrónico a la persona que tiene la información.', value: 5 },
                { text: 'Espera a una reunión o llama directamente a la persona.', value: 0 }
            ]
        },
        {
            text: '¿Con qué frecuencia se actualiza la documentación del equipo (procedimientos, manuales, etc.)?',
            options: [
                { text: 'La documentación se mantiene actualizada de manera regular, siendo un esfuerzo de todo el equipo.', value: 10 },
                { text: 'Solo se actualiza cuando hay un cambio mayor y es responsabilidad de una o dos personas.', value: 5 },
                { text: 'La documentación casi nunca se actualiza o no existe.', value: 0 }
            ]
        }
    ]
};

// Función para desordenar un array
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

// Función para renderizar las preguntas en el DOM
function renderQuestions() {
    const dim1Container = document.getElementById('dim1-container');
    const dim2Container = document.getElementById('dim2-container');
    const dim3Container = document.getElementById('dim3-container');

    // Desordenar las preguntas de cada dimensión
    shuffleArray(questions.dim1);
    shuffleArray(questions.dim2);
    shuffleArray(questions.dim3);

    // Función auxiliar para renderizar un conjunto de preguntas
    const render = (container, questionsArr, namePrefix) => {
        container.innerHTML = `<h2>${container.querySelector('h2').textContent}</h2>`; // Mantener el título
        questionsArr.forEach((q, index) => {
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question';
            questionDiv.textContent = `${index + 1}. ${q.text}`;

            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'options-container';

            // Desordenar las opciones de respuesta
            shuffleArray(q.options);

            q.options.forEach(option => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="radio" name="${namePrefix}-${index}" value="${option.value}" required> ${option.text} (${option.value} puntos)`;
                optionsDiv.appendChild(label);
            });
            questionDiv.appendChild(optionsDiv);
            container.appendChild(questionDiv);
        });
    };

    render(dim1Container, questions.dim1, 'q1');
    render(dim2Container, questions.dim2, 'q2');
    render(dim3Container, questions.dim3, 'q3');
}

// Llama a la función para renderizar las preguntas al cargar la página
window.onload = renderQuestions;

// Función para calcular el puntaje al hacer clic en el botón
function calculateScore() {
    const form = document.getElementById('imcacForm');
    const formData = new FormData(form);
    
    // Validar que todas las preguntas estén respondidas
    if (Array.from(form.querySelectorAll('input[type="radio"]:checked')).length !== 10) {
        alert('Por favor, responda todas las preguntas.');
        return;
    }

    let pd1 = 0;
    let pd2 = 0;
    let pd3 = 0;

    // Sumar los puntos por dimensión
    for (let i = 0; i < 4; i++) {
        pd1 += parseInt(formData.get(`q1-${i}`));
    }
    for (let i = 0; i < 4; i++) {
        pd2 += parseInt(formData.get(`q2-${i}`));
    }
    for (let i = 0; i < 2; i++) {
        pd3 += parseInt(formData.get(`q3-${i}`));
    }

    // Aplicar la fórmula ponderada
    const imcac = (pd1 / 40 * 30) + (pd2 / 40 * 30) + (pd3 / 20 * 40);

    let explanation = '';
    let emoji = '';
    if (imcac >= 90) {
        explanation = 'Nivel Pionero: El equipo tiene una cultura de comunicación asincrónica bien establecida. Sus procesos son eficientes y existe una clara corresponsabilidad por parte de todos los miembros.';
        emoji = '🚀';
    } else if (imcac >= 50) {
        explanation = 'Nivel Adaptativo: El equipo se encuentra en un proceso de transición. Ya ha adoptado algunas prácticas de comunicación asincrónica, pero aún existen áreas de mejora. Se requiere capacitación y el establecimiento de protocolos más claros.';
        emoji = '📈';
    } else {
        explanation = 'Nivel Básico: El equipo depende en gran medida de la comunicación sincrónica y enfrenta desafíos como la "responsabilidad diluida" y la baja calidad de los mensajes.';
        emoji = '🛠️';
    }

    // Mostrar el resultado en el DOM
    document.getElementById('imcac-score').textContent = `Su puntaje IMCAC es: ${imcac.toFixed(2)} / 100 ${emoji}`;
    document.getElementById('imcac-explanation').textContent = explanation;
    document.getElementById('result-container').style.display = 'block';
}