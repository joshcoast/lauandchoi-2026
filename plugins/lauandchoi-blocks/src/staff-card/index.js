import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	BlockControls,
	MediaPlaceholder,
	MediaReplaceFlow,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { ToolbarButton, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';
import './editor.scss';

function Edit( { attributes, setAttributes } ) {
	const { mediaId, name, jobTitle, bio } = attributes;
	const blockProps = useBlockProps( { className: 'lc-staff-card' } );

	// Only the attachment ID is saved, so the photo survives a domain change.
	const media = useSelect(
		( select ) =>
			mediaId
				? select( coreStore ).getEntityRecord(
						'postType',
						'attachment',
						mediaId
				  )
				: null,
		[ mediaId ]
	);
	const mediaUrl =
		media?.media_details?.sizes?.large?.source_url || media?.source_url;

	const onSelectImage = ( image ) =>
		setAttributes( { mediaId: image?.id } );

	const onRemoveImage = () => setAttributes( { mediaId: undefined } );

	return (
		<div { ...blockProps }>
			{ mediaId && (
				<BlockControls group="other">
					<MediaReplaceFlow
						mediaId={ mediaId }
						mediaURL={ mediaUrl }
						allowedTypes={ [ 'image' ] }
						accept="image/*"
						name={ __( 'Replace photo', 'lauandchoi-blocks' ) }
						onSelect={ onSelectImage }
					/>
					<ToolbarButton onClick={ onRemoveImage }>
						{ __( 'Remove photo', 'lauandchoi-blocks' ) }
					</ToolbarButton>
				</BlockControls>
			) }

			{ mediaId && ! mediaUrl && <Spinner /> }

			{ mediaId && mediaUrl && (
				<MediaUploadCheck>
					<MediaUpload
						value={ mediaId }
						allowedTypes={ [ 'image' ] }
						onSelect={ onSelectImage }
						render={ ( { open } ) => (
							<button
								type="button"
								className="lc-staff-card__photo-button"
								onClick={ open }
								aria-label={ __(
									'Replace photo',
									'lauandchoi-blocks'
								) }
							>
								<img
									className="lc-staff-card__photo"
									src={ mediaUrl }
									alt={ name }
								/>
							</button>
						) }
					/>
				</MediaUploadCheck>
			) }

			{ ! mediaId && (
				<MediaPlaceholder
					className="lc-staff-card__placeholder"
					icon="format-image"
					labels={ {
						title: __( 'Photo', 'lauandchoi-blocks' ),
						instructions: __(
							'Upload or choose a portrait. It will be cropped to the same shape as the other staff photos.',
							'lauandchoi-blocks'
						),
					} }
					allowedTypes={ [ 'image' ] }
					accept="image/*"
					onSelect={ onSelectImage }
				/>
			) }

			<div className="lc-staff-card__body">
				<RichText
					tagName="h2"
					className="lc-staff-card__name"
					value={ name }
					onChange={ ( value ) => setAttributes( { name: value } ) }
					placeholder={ __( 'Full name', 'lauandchoi-blocks' ) }
					allowedFormats={ [] }
					disableLineBreaks
				/>
				<RichText
					tagName="p"
					className="lc-staff-card__title"
					value={ jobTitle }
					onChange={ ( value ) =>
						setAttributes( { jobTitle: value } )
					}
					placeholder={ __( 'Job title', 'lauandchoi-blocks' ) }
					allowedFormats={ [] }
					disableLineBreaks
				/>
				<RichText
					tagName="p"
					className="lc-staff-card__bio"
					value={ bio }
					onChange={ ( value ) => setAttributes( { bio: value } ) }
					placeholder={ __( 'Short bio', 'lauandchoi-blocks' ) }
					allowedFormats={ [
						'core/bold',
						'core/italic',
						'core/link',
					] }
				/>
			</div>
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
	// Show the person's name in List View and the breadcrumb instead of "Staff Card".
	__experimentalLabel: ( { name } ) =>
		name ? name.replace( /<[^>]+>/g, '' ) : metadata.title,
} );
