/* global jQuery, UltimatumSEO */

import React from "react";
import ReactDOM from "react-dom";
import { KeywordSuggestions as KeywordSuggestionsComponent } from "ultimatum-premium-components";
import StyledSection from "ultimatum-components/forms/StyledSection/StyledSection";
import { translate } from "ultimatum-components/utils/i18n";

class KeywordSuggestions {
	constructor() {
		this.words = [];
	}

	/**
	 * Initializes into the DOM and adds all event handlers.
	 *
	 * @returns {void}
	 */
	initializeDOM() {
		this.appendSuggestionsDiv();
		this.renderComponent();

		jQuery( window ).on( "UltimatumSEO:numericScore", this.updateWords.bind( this ) );
	}

	/**
	 * Updates the words from the researcher.
	 *
	 * @returns {void}
	 */
	updateWords() {
		const researcher = UltimatumSEO.app.researcher;

		this.words = researcher.getResearch( "relevantWords" );

		this.renderComponent();
	}


	/**
	 * Appends the suggestions div to the DOM.
	 *
	 * @returns {void}
	 */
	appendSuggestionsDiv() {
		let contentDiv = jQuery( "#mcmsseo_content" );

		let tbody = contentDiv.find( "tbody" );
		let newRow = jQuery( "<tr><td></td></tr>");

		tbody.append( newRow );

		let td = newRow.find( "td" );

		this.suggestionsDiv = document.createElement( "div" );

		td.html( this.suggestionsDiv );
	}

	/**
	 * Initializes the component inside the suggestions div.
	 *
	 * @returns {void}
	 */
	renderComponent() {
		let keywordSuggestions = ( <KeywordSuggestionsComponent relevantWords={this.words} /> );
		let title = translate( "Insights" );

		ReactDOM.render(
			<StyledSection title={title} icon="file-text-o" sectionContent={keywordSuggestions} />,
			this.suggestionsDiv
		);
	}
}

export default KeywordSuggestions;
