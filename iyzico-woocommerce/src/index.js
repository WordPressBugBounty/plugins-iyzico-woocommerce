import domReady from '@wordpress/dom-ready'
import { createRoot } from '@wordpress/element'
import './index.css'

import App from "./App"

domReady(() => {
    const root = createRoot(
        document.getElementById('iyzico-app')
    )

    root.render(<App />)
})