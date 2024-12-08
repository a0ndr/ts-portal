<script lang="ts">
	import { onMount } from 'svelte';

	let icon: HTMLParagraphElement;
	let title: HTMLHeadingElement;
	let description: HTMLParagraphElement;
	let showDetails: HTMLParagraphElement;
	let details: string = '';
	let detailsElement: HTMLElement;
	let detailsToggled = false;
	let apiUrl: string;
	let code: string;
	let state: string;

	onMount(async () => {
		const params = new URLSearchParams(window.location.search);
		if (!params.get('code') || !params.get('state')) {
			icon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #f66151;"></i>';
			title.innerHTML = 'Error';
			description.innerHTML =
				'An error occurred while processing your request. Please try again or contact the administrator.';
			showDetails.innerText = 'Show Details';
			details = 'code or state not specified';

			document.getElementById('loading')?.classList.add('hidden');
			document.getElementById('processing')?.classList.remove('hidden');
		} else {
			document.getElementById('loading')?.classList.add('hidden');
			document.getElementById('confirmation')?.classList.remove('hidden');
		}
		code = params.get('code')!;
		state = params.get('state')!;
	});

	function process() {
		document.getElementById('confirmation')?.classList.add('hidden');
		document.getElementById('processing')?.classList.remove('hidden');

		try {
			fetch(`http://localhost:8080/callback`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					code,
					state
				})
			})
				.then((res) => {
					console.log(res);
					if (!res.ok) throw new Error('not ok: ' + res.status);
					icon.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #8ff0a4;"></i>';
					title.innerHTML = 'Success';
					description.innerHTML = 'You can close this window.';
				})
				.catch((err) => {
					icon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #f66151;"></i>';
					title.innerHTML = 'Error';
					description.innerHTML =
						'An error occurred while processing your request. Please try again or contact the administrator.';
					showDetails.innerText = 'Show Details';
					details = err;
				});
		} catch (e) {
			if (!(e instanceof Error)) return;
			icon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #f66151;"></i>';
			title.innerHTML = 'Error';
			description.innerHTML =
				'An error occurred while processing your request. Please try again or contact the administrator.';
			showDetails.innerText = 'Show Details';
			details = e.message;
		}
	}

	function cancel() {
		document.getElementById('confirmation')?.classList.add('hidden');
		document.getElementById('closed')?.classList.remove('hidden');
		window.close();
	}

	function toggle() {
		if (!detailsToggled) {
			showDetails.innerText = 'Hide Details';
			detailsElement.innerText = details;
			detailsToggled = true;
		} else {
			showDetails.innerText = 'Show Details';
			detailsElement.innerText = '';
			detailsToggled = false;
		}
	}
</script>

<link
	rel="stylesheet"
	href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
	integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
	crossorigin="anonymous"
	referrerpolicy="no-referrer"
/>

<div class="root" style="background-color: rgb(49, 49, 49); width: 100vw; height: 100vh;">
	<h1 class="name">tspace</h1>
	<div class="content loading" id="loading">
		<p class="icon"><span class="loader"></span></p>
	</div>
	<div class="confirmation hidden" id="confirmation">
		<h2 class="title">Confirm</h2>
		<p class="details">Are you sure you want to authorize?</p>
		<div id="actions">
			<button on:click={cancel} class="secondary">Cancel</button>
			<button on:click={process} class="primary">Authorize</button>
		</div>
	</div>
	<div class="confirmation hidden" id="closed">
		<h2 class="title">Canceled</h2>
		<p class="details">You can now close this window.</p>
	</div>
	<div class="content hidden" id="processing">
		<p class="icon" bind:this={icon}><span class="loader"></span></p>
		<h1 class="title" bind:this={title}>Processing</h1>
		<p class="details" bind:this={description}>
			We are processing your request. You will be redirected in a moment.
		</p>

		<!-- svelte-ignore a11y-no-noninteractive-element-to-interactive-role -->
		<!-- svelte-ignore a11y-click-events-have-key-events -->
		<p id="showDetails" on:click={toggle} role="button" class="hide" bind:this={showDetails}></p>
		<p class="details hide" id="errorWrapper">
			<code id="error" bind:this={detailsElement}></code>
		</p>
	</div>
</div>

<style>
	.name {
		color: white;
		padding-top: 5px;
		padding-left: 16px;
		font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		font-size: 24px;
	}

	.hidden {
		display: none;
	}

	* {
		margin: 0;
		padding: 0;
	}

	.root {
		width: 100%;
		height: 100%;
	}

	.content,
	.confirmation {
		top: 50%;
		left: 50%;
		position: absolute;
		transform: translate(-50%, -50%);
		color: white;
		text-align: center;
		font-family: sans-serif;
		width: 25%;
		border: 1px solid white;
		padding: 15px;
		padding-bottom: 20px;
		border-radius: 10px;
	}

	.icon {
		margin-top: 16px;
		font-size: 32px;
	}

	.title {
		margin: 0;
		padding: 0;
		margin-bottom: 5px;
	}

	p {
		margin-top: 12px;
		margin-bottom: 12px;
	}

	button.primary {
		padding: 8px;
		background-color: white;
		border: 0;
		border-radius: 5px;
	}

	button.primary:hover {
		background-color: #cecece;
		cursor: pointer;
	}

	button.secondary {
		padding: 8px;
		background-color: rgba(255, 255, 255, 0);
		color: white;
		border: 1px solid white;
		border-radius: 5px;
	}

	button.secondary:hover {
		background-color: #ffffff34;
		cursor: pointer;
	}

	.loader {
		width: 48px;
		height: 48px;
		border: 5px solid lightgray;
		border-bottom-color: transparent;
		border-radius: 50%;
		display: inline-block;
		box-sizing: border-box;
		animation: rotation 1s linear infinite;
	}

	@keyframes rotation {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}

	#showDetails {
		color: rgb(180, 180, 180);
		font-size: 12px;
		margin-top: 15px;
		margin-bottom: 10px;
		user-select: none;
	}

	#showDetails:hover {
		text-decoration: underline;
		cursor: pointer;
		color: white;
	}
</style>
