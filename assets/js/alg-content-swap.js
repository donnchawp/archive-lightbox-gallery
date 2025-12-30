/**
 * Archive Lightbox Gallery - Lightbox Script
 *
 * Handles the custom lightbox for the archive gallery.
 *
 * @package ArchiveLightboxGallery
 */

( function() {
	'use strict';

	/**
	 * Lightbox state and elements.
	 */
	const state = {
		lightbox: null,
		image: null,
		info: null,
		triggers: [],
		contentContainers: [],
		currentIndex: -1,
		totalImages: 0,
		isOpen: false
	};

	/**
	 * Initialize the lightbox functionality.
	 */
	function init() {
		// Get DOM elements.
		state.lightbox = document.querySelector( '.alg-lightbox' );
		state.image = document.querySelector( '.alg-lightbox-image' );
		state.info = document.querySelector( '.alg-lightbox-info' );
		state.triggers = Array.from( document.querySelectorAll( '.alg-lightbox-trigger' ) );
		state.contentContainers = Array.from( document.querySelectorAll( '.alg-post-content' ) );
		state.totalImages = state.triggers.length;

		if ( ! state.lightbox || ! state.triggers.length ) {
			return;
		}

		// Bind event handlers.
		bindEvents();
	}

	/**
	 * Bind all event handlers.
	 */
	function bindEvents() {
		// Image click handlers.
		state.triggers.forEach( function( trigger ) {
			trigger.addEventListener( 'click', handleTriggerClick );
			trigger.addEventListener( 'keydown', function( e ) {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					handleTriggerClick.call( this, e );
				}
			} );
		} );

		// Close button.
		const closeBtn = state.lightbox.querySelector( '.alg-lightbox-close' );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', closeLightbox );
		}

		// Overlay click to close.
		const overlay = state.lightbox.querySelector( '.alg-lightbox-overlay' );
		if ( overlay ) {
			overlay.addEventListener( 'click', closeLightbox );
		}

		// Navigation buttons.
		const prevBtn = state.lightbox.querySelector( '.alg-lightbox-prev' );
		const nextBtn = state.lightbox.querySelector( '.alg-lightbox-next' );

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', showPrevious );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', showNext );
		}

		// Keyboard navigation.
		document.addEventListener( 'keydown', handleKeydown );
	}

	/**
	 * Handle trigger click to open lightbox.
	 *
	 * @param {Event} e Click event.
	 */
	function handleTriggerClick( e ) {
		e.preventDefault();

		const trigger = e.currentTarget;
		const index = parseInt( trigger.getAttribute( 'data-alg-index' ), 10 );

		if ( ! isNaN( index ) ) {
			openLightbox( index );
		}
	}

	/**
	 * Open the lightbox at specified index.
	 *
	 * @param {number} index Image index to display.
	 */
	function openLightbox( index ) {
		state.currentIndex = index;
		state.isOpen = true;

		// Show lightbox.
		state.lightbox.removeAttribute( 'hidden' );
		document.body.style.overflow = 'hidden';

		// Load and display image.
		showImage( index );

		// Focus the close button for accessibility.
		const closeBtn = state.lightbox.querySelector( '.alg-lightbox-close' );
		if ( closeBtn ) {
			closeBtn.focus();
		}

		// Update navigation visibility.
		updateNavigation();
	}

	/**
	 * Close the lightbox.
	 */
	function closeLightbox() {
		state.isOpen = false;
		state.lightbox.setAttribute( 'hidden', '' );
		document.body.style.overflow = '';

		// Return focus to the trigger.
		if ( state.currentIndex >= 0 && state.triggers[ state.currentIndex ] ) {
			state.triggers[ state.currentIndex ].focus();
		}

		state.currentIndex = -1;
	}

	/**
	 * Show image at specified index.
	 *
	 * @param {number} index Image index.
	 */
	function showImage( index ) {
		const trigger = state.triggers[ index ];

		if ( ! trigger ) {
			return;
		}

		const fullSrc = trigger.getAttribute( 'data-alg-full-src' );
		const img = trigger.querySelector( 'img' );
		const alt = img ? img.getAttribute( 'alt' ) : '';

		// Update lightbox image.
		state.image.setAttribute( 'src', fullSrc );
		state.image.setAttribute( 'alt', alt );

		// Update post content.
		const content = state.contentContainers[ index ];
		if ( content && state.info ) {
			state.info.innerHTML = content.innerHTML;
		}

		state.currentIndex = index;
		updateNavigation();
	}

	/**
	 * Show previous image.
	 */
	function showPrevious() {
		if ( state.currentIndex > 0 ) {
			showImage( state.currentIndex - 1 );
		}
	}

	/**
	 * Show next image.
	 */
	function showNext() {
		if ( state.currentIndex < state.totalImages - 1 ) {
			showImage( state.currentIndex + 1 );
		}
	}

	/**
	 * Update navigation button visibility.
	 */
	function updateNavigation() {
		const prevBtn = state.lightbox.querySelector( '.alg-lightbox-prev' );
		const nextBtn = state.lightbox.querySelector( '.alg-lightbox-next' );

		if ( prevBtn ) {
			prevBtn.style.visibility = state.currentIndex > 0 ? 'visible' : 'hidden';
		}
		if ( nextBtn ) {
			nextBtn.style.visibility = state.currentIndex < state.totalImages - 1 ? 'visible' : 'hidden';
		}
	}

	/**
	 * Handle keyboard navigation.
	 *
	 * @param {KeyboardEvent} e Keyboard event.
	 */
	function handleKeydown( e ) {
		if ( ! state.isOpen ) {
			return;
		}

		switch ( e.key ) {
			case 'Escape':
				closeLightbox();
				break;
			case 'ArrowLeft':
				showPrevious();
				break;
			case 'ArrowRight':
				showNext();
				break;
		}
	}

	// Initialize when DOM is ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
