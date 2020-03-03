import React from 'react'

// User Management
import UserList from './users/Userlist'
import ProductList from './products/ProductList'
import Kanban from './Kanban'
import Calendar from './calendar/Calendars'
import Roles from './roles/Roles'
import Invoice from './invoice/Invoice'
import Companies from './companies/Companies'
import Categories from './categories/Categories'
import ProjectList from './forms/ProjectList'
import Leads from './forms/Leads'
import TaskList from './forms/TaskList'
import Customers from './customers/Customers'
import Departments from './departments/Departments'
import ChatPage from './chat/ChatPage'
import Dashboard from './Dashboard'
import Message from './activity/MessageContainer'
import UserProfile from './users/UserProfile'
import TaskStatus from './taskStatus/statusList'
import Permissions from './permissions/Permissions'
import Payments from './payments/Payments'
import TaxRates from './TaxRates/TaxRates'
import Credits from './credits/Credits'
import RecurringQuotes from './recurringQuotes/RecurringQuotes'
import RecurringInvoices from './recurringInvoices/RecurringInvoices'
import Quotes from './quotes/Quotes'
import Accounts from './accounts/Settings'
import TemplateSettings from './accounts/TemplateSettings'
import CustomFieldSettings from './accounts/CustomFieldSettings'
import EmailSettings from './accounts/EmailSettings'
import GatewaySettings from './accounts/GatewaySettings'
import InvoiceSettings from './accounts/InvoiceSettings'
import ProductSettings from './accounts/ProductSettings'
import NumberSettings from './accounts/NumberSettings'
import GroupSettings from './accounts/GroupSettings'
import Expenses from './expenses/Expenses'
import Designs from './designs/Designs'
import Integrations from './accounts/IntegrationSettings'

// https://github.com/ReactTraining/react-router/tree/master/packages/react-router-config
const routes = [
    {
        path: '/customers',
        name: 'Customers',
        component: Customers
    },
    {
        path: '/payments',
        name: 'Payments',
        component: Payments
    },
    {
        path: '/users',
        exact: true,
        name: 'Users',
        component: UserList
    },
    {
        path: '/products',
        name: 'Products',
        component: ProductList
    },
    {
        path: '/kanban/projects',
        name: 'Projects',
        component: Kanban
    },
    {
        path: '/kanban/leads',
        name: 'Leads',
        component: Kanban
    },
    {
        path: '/kanban/deals',
        name: 'Deals',
        component: Kanban
    },
    {
        path: '/calendar',
        exact: true,
        name: 'Calendar',
        component: Calendar
    },
    {
        path: '/roles',
        name: 'Roles',
        component: Roles
    },
    {
        path: '/invoice',
        name: 'Invoice',
        component: Invoice
    },
    {
        path: '/companies',
        name: 'Companies',
        component: Companies
    },
    {
        path: '/categories',
        name: 'Categories',
        component: Categories
    },
    {
        path: '/departments',
        name: 'Departments',
        component: Departments
    },
    {
        path: '/chat',
        name: 'Chat',
        component: ChatPage
    },
    {
        path: '/activity',
        name: 'Activity',
        component: Message
    },
    {
        path: '/statuses',
        name: 'Task Statuses',
        component: TaskStatus
    },
    {
        path: '/tax-rates',
        name: 'Tax Rates',
        component: TaxRates
    },
    {
        path: '/expenses',
        name: 'Expenses',
        component: Expenses
    },
    {
        path: '/permissions',
        name: 'Permissions',
        component: Permissions
    },
    {
        path: '/credits',
        name: 'Credits',
        component: Credits
    },
    {
        path: '/quotes',
        name: 'Quotes',
        component: Quotes
    },
    {
        path: '/accounts/:add?',
        name: 'Accounts',
        component: Accounts
    },
    {
        path: '/email-settings',
        name: 'Templates',
        component: EmailSettings
    },
    {
        path: '/gateway-settings',
        name: 'Gateway Settings',
        component: GatewaySettings
    },
    {
        path: '/invoice-settings',
        name: 'Invoice Settings',
        component: InvoiceSettings
    },
    {
        path: '/product-settings',
        name: 'Product Settings',
        component: ProductSettings
    },
    {
        path: '/template-settings',
        name: 'Email Settings',
        component: TemplateSettings
    },
    {
        path: '/email-settings',
        name: 'Email Settings',
        component: EmailSettings
    },
    {
        path: '/number-settings',
        name: 'Number Settings',
        component: NumberSettings
    },
    {
        path: '/group-settings',
        name: 'Group Settings',
        component: GroupSettings
    },
    {
        path: '/field-settings',
        name: 'Field Settings',
        component: CustomFieldSettings
    },
    {
        path: '/designs',
        name: 'Designs',
        component: Designs
    },
    {
        path: '/integrations',
        name: 'Integrations',
        component: Integrations
    },
    {
        path: '/recurring-quotes',
        name: 'Recurring Quotes',
        component: RecurringQuotes
    },
    {
        path: '/recurring-invoices',
        name: 'Recurring Invoices',
        component: RecurringInvoices
    },
    {
        path: '/tasks',
        exact: true,
        name: 'Task List',
        component: TaskList
    },
    {
        path: '/projects',
        exact: true,
        name: 'Project List',
        component: ProjectList
    },
    {
        path: '/leads',
        exact: true,
        name: 'Leads List',
        component: Leads
    },
    {
        path: '/users/:username',
        exact: true,
        name: 'User Details',
        component: UserProfile
    },
    {
        path: '/',
        name: 'Dashboard',
        component: Dashboard
    }
    // {path: '/base/list-groups', name: 'List Groups', component: ListGroups},
    // {path: '/base/navbars', name: 'Navbars', component: Navbars},
    // {path: '/base/navs', name: 'Navs', component: Navs},
    // {path: '/base/paginations', name: 'Paginations', component: Paginations},
    // {path: '/base/popovers', name: 'Popovers', component: Popovers},
    // {path: '/base/progress-bar', name: 'Progress Bar', component: ProgressBar},
    // {path: '/base/tooltips', name: 'Tooltips', component: Tooltips},
    // {path: '/buttons', exact: true, name: 'Buttons', component: Buttons},
    // {path: '/buttons/buttons', name: 'Buttons', component: Buttons},
    // {path: '/buttons/button-dropdowns', name: 'Button Dropdowns', component: ButtonDropdowns},
    // {path: '/buttons/button-groups', name: 'Button Groups', component: ButtonGroups},
    // {path: '/buttons/brand-buttons', name: 'Brand Buttons', component: BrandButtons},
    // {path: '/icons', exact: true, name: 'Icons', component: CoreUIIcons},
    // {path: '/icons/coreui-icons', name: 'CoreUI Icons', component: CoreUIIcons},
    // {path: '/icons/flags', name: 'Flags', component: Flags},
    // {path: '/icons/font-awesome', name: 'Font Awesome', component: FontAwesome},
    // {path: '/icons/simple-line-icons', name: 'Simple Line Icons', component: SimpleLineIcons},
    // {path: '/notifications', exact: true, name: 'Notifications', component: Alerts},
    // {path: '/notifications/alerts', name: 'Alerts', component: Alerts},
    // {path: '/notifications/badges', name: 'Badges', component: Badges},
    // {path: '/notifications/modals', name: 'Modals', component: Modals},
    // {path: '/widgets', name: 'Widgets', component: Widgets},
    // {path: '/charts', name: 'Charts', component: Charts},
    // {path: '/users', exact: true, name: 'Users', component: Users},
    // {path: '/features', exact: true, name: 'Features', component: Features},
    // {path: '/organisations', exact: true, name: 'Organisations', component: Organisations},
    // {path: '/organisation-units', exact: true, name: 'Organisation Units', component: OrganisationUnits},
    // {path: '/roles', exact: true, name: 'Roles', component: Roles},
    // {path: '/applications', exact: true, name: 'Applications', component: Applications}
    // { path: '/users/:id', exact: true, name: 'User Details', component: User },
]

export default routes
