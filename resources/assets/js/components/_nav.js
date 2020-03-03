export default {
    items: [

        {
            name: 'Dashboard',
            url: '/dashboard',
            icon: 'fa fa-dashboard'
        },

        {
            icon: 'fa fa-cog',
            name: 'Settings',
            children: [
                {
                    name: 'Company Details',
                    url: '/accounts',
                    icon: 'fa fa-building'
                },
                {
                    name: 'Templates and Reminders',
                    url: '/template-settings',
                    icon: 'fa fa-exclamation-triangle'
                },
                {
                    name: 'Email Settings',
                    url: '/email-settings',
                    icon: 'fa fa-envelope'
                },
                {
                    name: 'Online Payments',
                    url: '/gateway-settings',
                    icon: 'fa fa-credit-card-alt'
                },
                {
                    name: 'Invoice and Quotes',
                    url: '/invoice-settings',
                    icon: 'fa fa-user'
                },
                {
                    name: 'Products',
                    url: '/product-settings',
                    icon: 'fa fa-barcode'
                },
                {
                    name: 'Generated Numbers',
                    url: '/number-settings',
                    icon: 'fa fa-list'
                },
                {
                    name: 'Groups',
                    url: '/group-settings',
                    icon: 'fa fa-group'
                },
                {
                    name: 'Tax Rates',
                    url: '/tax-rates',
                    icon: 'fa fa-dashboard'
                },
                {
                    name: 'Field Settings',
                    url: '/field-settings',
                    icon: 'fa fa-dashboard'
                },
                {
                    name: 'Designs',
                    url: '/designs',
                    icon: 'fa fa-dashboard'
                },
                {
                    name: 'Integrations',
                    url: '/integrations',
                    icon: 'fa fa-dashboard'
                }
            ]
        },
        {
            name: 'User Management',
            icon: 'fa fa-dashboard',
            children: [
                {
                    name: 'Employees',
                    url: '/users',
                    icon: 'fa fa-user'
                },

                {
                    name: 'Departments',
                    url: '/departments',
                    icon: 'fa fa-sitemap'
                },

                {
                    name: 'Roles',
                    url: '/roles',
                    icon: 'fa fa-chain-broken'
                },

                {
                    name: 'Permissions',
                    url: '/permissions',
                    icon: 'fa fa-list-alt'
                }
            ]
        },

        {
            name: 'Companies',
            url: '/companies',
            icon: 'fa fa-building'
        },

        {
            name: 'Tasks',
            icon: 'fa fa-chain-broken',
            children: [
                {
                    name: 'Projects',
                    url: 'projects',
                    icon: 'fa fa-suitcase'
                },
                {
                    name: 'Tasks',
                    url: 'tasks',
                    icon: 'fa fa-clock-o'
                },
                {
                    name: 'Leads Kanban',
                    url: '/kanban/leads',
                    icon: 'fa fa-chain-broken'
                },
                {
                    name: 'Leads',
                    url: '/leads',
                    icon: 'fa fa-chain-broken'
                },

                {
                    name: 'Deals',
                    url: '/kanban/deals',
                    icon: 'fa fa-chain-broken'
                },
                {
                    name: 'Task Statuses',
                    url: '/statuses',
                    icon: 'fa fa-building'
                }
            ]
        },

        {
            name: 'Financial',
            icon: 'fa fa-bar-chart',
            children: [
                {
                    name: 'Invoices',
                    url: '/invoice',
                    icon: 'fa fa-area-chart'
                },
                {
                    name: 'Quotes',
                    url: '/quotes',
                    icon: 'fa fa-handshake-o'
                },

                {
                    name: 'Recurring Invoices',
                    url: '/recurring-invoices',
                    icon: 'fa fa-area-chart'
                },
                {
                    name: 'Recurring Quotes',
                    url: '/recurring-quotes',
                    icon: 'fa fa-handshake-o'
                },
                {
                    name: 'Expenses',
                    url: '/expenses',
                    icon: 'fa fa-bar-chart-o'
                },
                {
                    name: 'Payments',
                    url: '/payments',
                    icon: 'fa fa-credit-card-alt'
                },
                {
                    name: 'Orders',
                    url: '/orders',
                    icon: 'fa fa-shopping-basket'
                }
            ]
        },

        {
            name: 'Products',
            icon: 'fa fa-barcode',
            children: [
                {
                    name: 'Products',
                    url: '/products',
                    icon: 'fa fa-barcode'
                },

                {
                    name: 'Categories',
                    url: '/categories',
                    icon: 'fa fa-building'
                }
            ]
        },

        {
            name: 'Customers',
            url: '/customers',
            icon: 'fa fa-address-book-o'
        },
        {
            name: 'Calendar',
            url: '/calendar',
            icon: 'fa fa-chain-broken'
        },

        {
            name: 'Chat',
            url: '/chat',
            icon: 'fa fa-chain-broken'
        }
    ]
}
