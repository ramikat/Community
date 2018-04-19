<?php
/**
 * This file is part of Community plugin for FacturaScripts.
 * Copyright (C) 2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\Community\Controller;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Plugins\Community\Model\WebDocPage;
use FacturaScripts\Plugins\Community\Model\WebProject;
use FacturaScripts\Plugins\webportal\Lib\WebPortal\PortalController;

/**
 * Description of WebDocumentation
 *
 * @author Carlos García Gómez
 */
class WebDocumentation extends PortalController
{

    /**
     *
     * @var WebProject
     */
    public $currentProject;

    /**
     *
     * @var WebDocPage[]
     */
    public $docPages;

    /**
     *
     * @var WebProject[]
     */
    public $projects;

    public function getPageData()
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'documentation';
        $pageData['menu'] = 'web';
        $pageData['icon'] = 'fa-book';

        return $pageData;
    }

    public function getProjectUrl(WebProject $project)
    {
        if ('*' === substr($this->webPage->permalink, -1)) {
            return substr($this->webPage->permalink, 1, -1) . $project->idproject;
        }

        return substr($this->webPage->permalink, 1) . '/' . $project->idproject;
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);
        $this->loadProject();
    }

    public function publicCore(&$response)
    {
        parent::publicCore($response);
        $this->loadProject();
    }

    private function loadProject()
    {
        $this->setTemplate('WebDocumentation');

        /// current project
        $this->currentProject = new WebProject();
        $this->currentProject->loadFromCode(AppSettings::get('community', 'idproject', ''));

        /// all projects
        $this->projects = $this->currentProject->all([], ['name' => 'ASC'], 0, 0);

        /// Doc pages for this project
        $webDocPage = new WebDocPage();
        $where = [new DataBaseWhere('idproject', $this->currentProject->idproject)];
        $this->docPages = $webDocPage->all($where, [], 0, 0);
    }
}
