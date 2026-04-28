Admin CRUD standard:

Store/Update:
FormRequest → Data → Service → Repository

Index:
Request → IndexData → Repository paginate → ListItemData output

Detail/Edit:
Model → DetailData

Controller:
csak HTTP/Inertia/redirect/flash

Service:
üzleti szabályok, slug, tranzakció

Repository:
query, filter, pagination, persistence

Data:
typed input/output boundary
