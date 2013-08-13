Architecture and API Design for Taxonomy and Channel Mechanism


Term
    Aggregator – Channel or column to aggregate contents from various providers
    Provider – Modules to provide contents for aggregator
    Taxonomy – Global structural data schema for contents between aggregator and provider
Abbreviation
    S – Structural schema
    E – Entity
    R – Relation

Provider (E) <----> Aggregator (R) <---- Taxonomy (S)

APIs - Taxonomy
For aggregator
    get -> model
    getTree: array/nested
    getList: array
More
    @see Pi\Application\Service\Taxonomy

APIs - Aggregator
Global
    add: name, title (, slug, theme) -> bool
    update: name, title (, slug, theme) -> bool
    get: name -> array
    has: name -> bool
    delete: name -> bool
    setTheme: name, themeName -> bool
    getTheme: name -> string
    getList -> array
    getUri: name -> string
Page related
    addPage: name, title, type, channel (, provider, slug, cache, access, layout) -> bool
    updatePage: name, title  (, slug, cache, access, layout) -> bool
    deletePage: name -> bool
Provider related
    addProvider: name, title, slug [, meta] -> bool
    upateProvider: name, title, slug [, meta] -> bool
    hasProvider: name -> bool
    getProvider: name -> array
    getProviders -> array
Taxon/Channel related
    getTaxonomy -> tree
    hasTaxon: name -> bool
    getTaxon: name -> array
    getTaxonUri: name -> string
Entity related
    hasEntity: module, [id (, category)] -> bool
    getEntity: module, [id, (, category)] -> array
    addEntity: taxon, module, [id, (, category)], (, slug, time) -> bool
    updateEntity: module, [id, (, category)] (, slug, time) -> bool
    deleteEntity: module, [id, (, category)] -> bool
    getUri: module, [id, (, category)] -> string:uri

    hasPush: taxon, module, [id, (, category)]  -> bool
    getPushes: module, [id, (, category)] -> array
    addPush: taxon, module, [id, (, category)] -> bool
    updatePush: taxon, module, [id, (, category)] (, slug) -> bool
    deletePush: taxon, module, [id, (, category)] -> bool

APIs - Provider
For aggregator
    isActive -> bool
    hasEntity: id -> bool
    getEntity: id -> array
    getList: id[](, fields[]) -> array
    renderEntity: id (, template) -> string
    renderList: id[] (, template) -> string




Taiwen Jiang
October 22nd, 2012

Attachment:

<?php
namespace   Module\Provider\Api;

use Pi\Application\AbstractProvider;

class Provider extends AbstractProvider
{
    public function renderEntity($id, $template = '')
    {
        if (!$template) {
            $template = '...';
        }
        $model = Pi::model('provider', $this->module);
        $row = $model->find($id);
        $viewModel = $this->getViewModel($row->toArray());
        $viewModel->setTemplate($template);
        $content = $this->getRenderer()->render($viewModel);

        return $content;
    }
}

<?php
$providerHandler = Pi::api('provider', 'provider');
$entityContent = $providerHandler->renderEntity($id);