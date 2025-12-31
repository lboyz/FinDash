import Auth from './Auth'
import DashboardController from './DashboardController'
import TransactionController from './TransactionController'
import Settings from './Settings'
import ExportController from './ExportController'


const Controllers = {
    Auth: Object.assign(Auth, Auth),
    DashboardController: Object.assign(DashboardController, DashboardController),
    TransactionController: Object.assign(TransactionController, TransactionController),
    Settings: Object.assign(Settings, Settings),
    ExportController: Object.assign(ExportController, ExportController),
}

export default Controllers