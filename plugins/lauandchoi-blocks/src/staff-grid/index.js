import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	InnerBlocks,
	ButtonBlockAppender,
} from '@wordpress/block-editor';

import metadata from './block.json';
import './style.scss';

const ALLOWED_BLOCKS = [ 'lauandchoi/staff-card' ];
const TEMPLATE = [ [ 'lauandchoi/staff-card' ] ];

function Edit( { clientId } ) {
	const blockProps = useBlockProps( { className: 'lc-staff-grid' } );
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		allowedBlocks: ALLOWED_BLOCKS,
		template: TEMPLATE,
		renderAppender: () => (
			<ButtonBlockAppender
				rootClientId={ clientId }
				className="lc-staff-grid__appender"
			/>
		),
	} );

	return <div { ...innerBlocksProps } />;
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
} );
