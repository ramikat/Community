<?php
/**
 * This file is part of Community plugin for FacturaScripts.
 * Copyright (C) 2018 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\Community\Controller;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Plugins\Community\Lib\WebPortal\PortalControllerWizard;
use FacturaScripts\Plugins\Community\Model\ContactFormTree;
use FacturaScripts\Plugins\Community\Model\Issue;
use FacturaScripts\Plugins\Community\Model\WebProject;

/**
 * This class allow us to manage new issues.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class AddIssue extends PortalControllerWizard
{

    /**
     * The new issue.
     *
     * @var Issue
     */
    public $issue;

    /**
     * Returns the tree list of related pages.
     *
     * @return array
     */
    public function getTreeList(): array
    {
        $list = [];

        $idTree = $this->request->get('idtree', '');
        $contactFormTree = new ContactFormTree();
        if (!empty($idTree) && $contactFormTree->loadFromCode($idTree)) {
            array_unshift($list, $contactFormTree->title);
            $parent = $contactFormTree->getParentPage();
            while ($parent && $parent->exists()) {
                array_unshift($list, $parent->title);
                $parent = $parent->getParentPage();
            }
        }

        $idproject = $this->request->get('idproject', '');
        $pluginProject = new WebProject();
        if (!empty($idproject) && $pluginProject->loadFromCode($idproject)) {
            $list[] = $pluginProject->name;
        }

        return $list;
    }

    /**
     * 
     * @return int
     */
    public function pointCost()
    {
        return 0;
    }

    /**
     * Execute common code between private and public core.
     */
    protected function commonCore()
    {
        $this->setTemplate('AddIssue');
        $this->issue = new Issue();

        $body = $this->request->get('body', '');
        if (empty($body)) {
            return;
        }

        /// save issue
        $this->issue->body = $body;
        $this->issue->creationroute = implode(', ', $this->getTreeList());
        $this->issue->idcontacto = $this->contact->idcontacto;
        $this->issue->idproject = $this->request->get('idproject');
        $this->issue->idteam = AppSettings::get('community', 'idteamsup');
        $this->issue->idtree = $this->request->get('idtree');
        if ($this->issue->save()) {
            $this->miniLog->notice($this->i18n->trans('record-updated-correctly'));
            $this->subtractPoints();

            /// redit to new issue
            $this->response->headers->set('Refresh', '0; ' . $this->issue->url('public'));
            return;
        }

        $this->miniLog->alert($this->i18n->trans('record-save-error'));
    }

    protected function subtractPoints()
    {
        $this->contact->puntos -= $this->pointCost();
        $this->contact->save();
    }
}
