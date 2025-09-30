document.addEventListener('DOMContentLoaded', function () {
	// =================================================================
	// 1. ELEMENTOS DEL DOM
	// =================================================================
	const form = document.getElementById('imcacForm');
	const resultDiv = document.getElementById('result');
	const totalScoreSpan = document.getElementById('totalScore');
	const maturityLevelSpan = document.getElementById('maturityLevel');
	const maturityExplanationP = document.getElementById('maturityExplanation');
	const resultNombreSpan = document.getElementById('resultNombre');
	const resultAreaSpan = document.getElementById('resultArea');
	const resultRolSpan = document.getElementById('resultRol');
	const resultEmailSpan = document.getElementById('resultEmail');
	const submitTopBtn = document.getElementById('submitTopBtn');
	const progressBar = document.getElementById('progressBar');
	const progressCounter = document.getElementById('progressCounter');
	const completionIcon = document.getElementById('completionIcon');
	const radarChartCanvas = document.getElementById('radarChart');

	const totalScoredQuestions = 10;
	let radarChartInstance = null;

	// =================================================================
	// 2. FUNCIONES DE LÓGICA
	// =================================================================

	/**
	 * Desordena un array en el lugar usando el algoritmo Fisher-Yates.
	 * @param {Array} array El array a desordenar.
	 * @returns {Array} El mismo array, desordenado.
	 */
	function shuffleArray(array) {
		for (let i = array.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			[array[i], array[j]] = [array[j], array[i]];
		}
		return array;
	}

	/**
	 * Calcula los puntajes por dimensión a partir del formulario.
	 * @returns {Object} Un objeto con los puntajes { scoreD1, scoreD2, scoreD3 }.
	 */
	function calculateDimensionScores() {
		let scoreD1 = 0,
			scoreD2 = 0,
			scoreD3 = 0;

		document.querySelectorAll('.option-list[data-question-id]').forEach(questionDiv => {
			const questionId = questionDiv.dataset.questionId;
			const selectedOption = form.querySelector(`input[name="${questionId}"]:checked`);

			if (selectedOption) {
				const points = parseInt(selectedOption.dataset.points);
				const dimensionId = questionDiv.closest('.card').id;

				if (dimensionId === 'dimension1') scoreD1 += points;
				else if (dimensionId === 'dimension2') scoreD2 += points;
				else if (dimensionId === 'dimension3') scoreD3 += points;
			}
		});
		return { scoreD1, scoreD2, scoreD3 };
	}

	/**
	 * Calcula el nivel de madurez y la explicación basada en el puntaje total.
	 * @param {number} imcacScore El puntaje total del IMCAC.
	 * @returns {Object} Un objeto con { maturityLevel, explanation, alertClass }.
	 */
	function computeMaturityLevel(imcacScore) {
		let maturityLevel = '',
			explanation = '',
			alertClass = 'alert-primary';

		if (imcacScore >= 90) {
			maturityLevel = 'Madurez Alta - Nivel Pionero.';
			explanation = 'El equipo tiene una cultura de comunicación asincrónica bien establecida. Sus procesos son eficientes, la comunicación fluye sin interrupciones y existe una clara corresponsabilidad por parte de todos los miembros. El equipo en este nivel ya aplica los principios de "comunicación asincrónica" y "colaborativa" lo que se traduce en un "ecosistema de trabajo autónomo, transparente y altamente productivo."';
			alertClass = 'alert-success';
		} else if (imcacScore >= 50) {
			maturityLevel = 'Madurez Media - Nivel Adaptativo.';
			explanation = 'El equipo se encuentra en un proceso de transición y ha comenzado su camino hacia la adopción de la comunicación asincrónica. Si bien ya utiliza algunas herramientas y prácticas, aún hay inconsistencias y falta de protocolos claros. Se requiere capacitación para el establecimiento de protocolos más claros y  estrategias para organizar y buscar información de forma eficiente';
			alertClass = 'alert-warning';
		} else {
			maturityLevel = 'Madurez Baja - Nivel Básico.';
			explanation = 'Este nivel de madurez refleja una fuerte dependencia de las interacciones sincrónicas como las reuniones, las llamadas y la buena memoria de los integrantes del equipo, enfrentando desafíos para lograr una comunicación clara y efectiva. Existe una gran oportunidad para iniciar la transformación cultural.';
			alertClass = 'alert-danger';
		}
		return { maturityLevel, explanation, alertClass };
	}

	// =================================================================
	// 3. FUNCIONES DE RENDERIZADO Y UI
	// =================================================================

	/**
	 * Actualiza la barra de progreso y el contador de respuestas.
	 */
	function updateProgress() {
		const answeredQuestions = document.querySelectorAll('input[name^="q"]:not([name="q0"]):checked').length;
		const progress = totalScoredQuestions > 0 ? (answeredQuestions / totalScoredQuestions) * 100 : 0;
		const roundedProgress = Math.round(progress);

		progressBar.style.width = `${roundedProgress}%`;
		progressBar.textContent = `${roundedProgress}%`;
		progressBar.setAttribute('aria-valuenow', roundedProgress);
		progressCounter.textContent = `${answeredQuestions}/${totalScoredQuestions} respondidas`;

		if (roundedProgress === 100) {
			submitTopBtn.disabled = false;
			completionIcon.style.display = 'inline-block';
		} else {
			submitTopBtn.disabled = true;
			completionIcon.style.display = 'none';
		}
	}

	/**
	 * Aplica feedback visual a las opciones seleccionadas después de enviar.
	 */
	function applyVisualFeedback() {
		// Limpiar feedback anterior
		document.querySelectorAll('.option').forEach(option => {
			option.classList.remove('selected', 'green', 'yellow', 'danger');
			const feedback = option.querySelector('.feedback');
			if (feedback) feedback.style.display = 'none';
		});

		// Aplicar nuevo feedback
		document.querySelectorAll('input[name^="q"]:checked').forEach(selectedOption => {
			const questionId = selectedOption.name;
			if (questionId === 'q0') return;

			const points = parseInt(selectedOption.dataset.points);
			const selectedContainer = selectedOption.closest('.option');
			let colorClass = '';

			if (points === 10) colorClass = 'green';
			else if (points === 5) colorClass = 'yellow';
			else colorClass = 'danger';

			selectedContainer.classList.add('selected', colorClass);
			const feedbackDiv = selectedContainer.querySelector('.feedback');
			if (feedbackDiv) {
				feedbackDiv.style.display = 'block';
			}
		});
	}

	/**
	 * Renderiza el gráfico de radar con los puntajes de las dimensiones.
	 * @param {Object} scores - Objeto con { scoreD1, scoreD2, scoreD3 }.
	 */
	function renderRadarChart({ scoreD1, scoreD2, scoreD3 }) {
		const TPMD1 = 40, TPMD2 = 40, TPMD3 = 20;
		const data = {
			labels: ['Herramientas (30%)', 'Calidad Mensajes (40%)', 'Cultura (30%)'],
			datasets: [{
				label: 'Puntaje por Dimensión (%)',
				data: [
					(scoreD1 / TPMD1) * 100,
					(scoreD2 / TPMD2) * 100,
					(scoreD3 / TPMD3) * 100
				],
				fill: true,
				backgroundColor: 'rgba(54, 162, 235, 0.2)',
				borderColor: 'rgb(54, 162, 235)',
				pointBackgroundColor: 'rgb(54, 162, 235)',
				pointBorderColor: '#fff',
				pointHoverBackgroundColor: '#fff',
				pointHoverBorderColor: 'rgb(54, 162, 235)'
			}]
		};

		if (radarChartInstance) {
			radarChartInstance.destroy();
		}

		radarChartInstance = new Chart(radarChartCanvas, {
			type: 'radar',
			data: data,
			options: {
				elements: { line: { borderWidth: 3 } },
				scales: { r: { angleLines: { display: false }, suggestedMin: 0, suggestedMax: 100 } },
				maintainAspectRatio: false
			}
		});
	}

	// =================================================================
	// 4. MANEJADORES DE EVENTOS Y EJECUCIÓN INICIAL
	// =================================================================

	// Desordenar opciones de respuesta al cargar
	document.querySelectorAll('.option-list').forEach(optionList => {
		if (optionList.dataset.questionId !== 'q0') {
			const options = Array.from(optionList.children);
			shuffleArray(options).forEach(option => optionList.appendChild(option));
		}
	});

	// Actualizar progreso al cambiar una respuesta
	form.addEventListener('change', (event) => {
		if (event.target.type === 'radio' && event.target.name !== 'q0') {
			updateProgress();
		}
	});

	// Procesar el formulario al enviarlo
	form.addEventListener('submit', (event) => {
		event.preventDefault();
		if (document.querySelectorAll('input[name^="q"]:not([name="q0"]):checked').length < totalScoredQuestions) {
			alert('Por favor, completa todas las preguntas antes de calcular el resultado.');
			return;
		}

		applyVisualFeedback();
		const { scoreD1, scoreD2, scoreD3 } = calculateDimensionScores();
		const TPMD1 = 40, TPMD2 = 40, TPMD3 = 20;
		const imcacScore = (scoreD1 * 30) / TPMD1 + (scoreD2 * 40) / TPMD2 + (scoreD3 * 30) / TPMD3;
		const { maturityLevel, explanation, alertClass } = computeMaturityLevel(imcacScore);

		// Poblar resultados
		resultNombreSpan.textContent = document.getElementById('nombre').value;
		resultAreaSpan.textContent = document.getElementById('area').value;
		resultRolSpan.textContent = document.getElementById('rol').value;
		resultEmailSpan.textContent = document.getElementById('email').value;
		totalScoreSpan.textContent = `${imcacScore.toFixed(2)}%`;
		maturityLevelSpan.textContent = maturityLevel;
		maturityExplanationP.textContent = explanation;
		resultDiv.className = `alert ${alertClass} mt-5 p-4`;

		// Renderizar gráfico y mostrar resultados
		renderRadarChart({ scoreD1, scoreD2, scoreD3 });
		resultDiv.style.display = 'block';
		resultDiv.scrollIntoView({ behavior: 'smooth' });
	});
});