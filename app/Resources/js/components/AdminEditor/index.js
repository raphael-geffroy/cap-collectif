// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import NewEditor from './Editor';
import LegacyEditor from '../Form/Editor';
import uploadLocalImagePlugin from './plugins/uploadLocalImage';
import type { GlobalState } from '../../types';

// TODO: during the switch we can not add correct props, types
// Once the feature flag is removed, please fix this.
type Props = Object;

// Prevent usage via feature flag
class EditorBehindFeatureFlag extends React.Component<Props> {
  onAdminEditorChange = (name: string, state: {| html: string, raw: ?Object |}): void => {
    const { onChange } = this.props;

    if (onChange) {
      onChange(state.html);
    }
  };

  render() {
    if (!this.props.isNewEditorEnabled) {
      return <LegacyEditor {...this.props} />;
    }
    return (
      <NewEditor
        {...this.props}
        uploadLocalImage={uploadLocalImagePlugin}
        onContentChange={this.onAdminEditorChange}
      />
    );
  }
}

const mapStateToProps = (state: GlobalState) => {
  return {
    isNewEditorEnabled: !!state.default.features.unstable__admin_editor,
  };
};

export default connect(mapStateToProps)(EditorBehindFeatureFlag);