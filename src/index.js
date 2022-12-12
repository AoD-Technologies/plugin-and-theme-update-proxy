import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'

import {
  createTheme,
  ThemeProvider
} from '@mui/material/styles'

import 'whatwg-fetch'

import 'typeface-roboto'

import App from './App'

const adminColorDetector = document.createElement('a')
adminColorDetector.style.display = 'none'
document.getElementById('adminmenu').appendChild(adminColorDetector)
const adminColorDetectorStyle = global.getComputedStyle(adminColorDetector)

const palette = {
  primary: {},
  secondary: {}
}

adminColorDetector.setAttribute('class', 'button-primary')
palette.primary.main = adminColorDetectorStyle.backgroundColor

adminColorDetector.setAttribute('class', 'awaiting-mod')
palette.secondary.main = adminColorDetectorStyle.backgroundColor

adminColorDetector.remove()

const theme = createTheme({
  palette,
  components: {
    MuiOutlinedInput: {
      styleOverrides: {
        input: {
          padding: '18.5px 14px !important'
        },
        inputSizeSmall: {
          paddingBottom: '10.5px !important',
          paddingTop: '10.5px !important'
        }
      }
    },
    MuiInputBase: {
      styleOverrides: {
        input: {
          background: 'none !important',
          backgroundColor: 'none !important',
          border: '0 !important',
          borderRadius: '0 !important',
          boxShadow: 'none !important',
          color: 'currentColor !important',
          lineHeight: 'inherit !important',
          minHeight: '0 !important',
          outline: '0 !important'
        }
      }
    }
  }
})

createRoot(document.getElementById('ptup-root')).render(
  <StrictMode>
    <ThemeProvider theme={theme}>
      <App />
    </ThemeProvider>
  </StrictMode>
)
