import { makeStyles } from '@material-ui/core/styles'

import AppBar from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/AppBar'
import SnackBar from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/SnackBar'
import SpeedDial from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/SpeedDial'
import Tabs from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/components/Tabs'

import { AddAuthenticationTokenDialogOpenProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/AddAuthenticationTokenDialogOpen'
import { AddSourceDialogOpenProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/AddSourceDialogOpen'
import { VisibleTabProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/VisibleTab'
import { WordPressPluginsProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/WordPressPlugins'
import { WordPressThemesProvider } from '@aodtechnologies/plugin-and-theme-update-proxy-components/lib/providers/WordPressThemes'

const useStyles = makeStyles(theme => ({
  root: {
    border: '1px solid #ddd',
    minHeight: 200,
    backgroundColor: theme.palette.background.paper
  }
}))

const App = () => {
  const classes = useStyles()

  return (
    <AddAuthenticationTokenDialogOpenProvider>
      <AddSourceDialogOpenProvider>
        <VisibleTabProvider>
          <WordPressPluginsProvider>
            <WordPressThemesProvider>
              <div className={`ptup-ui ${classes.root}`}>
                <AppBar />
                <Tabs />
                <SpeedDial />
                <SnackBar />
              </div>
            </WordPressThemesProvider>
          </WordPressPluginsProvider>
        </VisibleTabProvider>
      </AddSourceDialogOpenProvider>
    </AddAuthenticationTokenDialogOpenProvider>
  )
}

export default App
