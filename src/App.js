import { styled } from '@mui/material/styles'

import AppBar from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/AppBar'
import SnackBar from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/SnackBar'
import SpeedDial from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/SpeedDial'
import Tabs from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/Tabs'

import { AddAuthenticationTokenDialogOpenProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/AddAuthenticationTokenDialogOpen'
import { AddSourceDialogOpenProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/AddSourceDialogOpen'
import { VisibleTabProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/VisibleTab'
import { WordPressPluginsProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/WordPressPlugins'
import { WordPressThemesProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/WordPressThemes'

const Div = styled('div')``

const App = () => (
  <AddAuthenticationTokenDialogOpenProvider>
    <AddSourceDialogOpenProvider>
      <VisibleTabProvider>
        <WordPressPluginsProvider>
          <WordPressThemesProvider>
            
            <Div className='ptup-ui' sx={{
              border: '1px solid #ddd',
              minHeight: 200,
              bgcolor: 'background.paper'
            }}>
              <AppBar />
              <Tabs />
              <SpeedDial />
              <SnackBar />
            </Div>
          </WordPressThemesProvider>
        </WordPressPluginsProvider>
      </VisibleTabProvider>
    </AddSourceDialogOpenProvider>
  </AddAuthenticationTokenDialogOpenProvider>
)

export default App
