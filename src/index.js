import ReactDOM from 'react-dom'

import ThemeProvider from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/ThemeProvider'

import App from './App'

ReactDOM.render(
    <ThemeProvider>
        <App />
    </ThemeProvider>,
    document.getElementById('ptup-root')
)
